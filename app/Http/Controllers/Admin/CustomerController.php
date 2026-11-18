<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'customer')->withCount('orders')->withSum(['orders as total_spent' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'total');
        if ($q = trim((string) $request->input('q'))) {
            $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"));
        }
        if ($request->input('status') === 'blocked') {
            $query->where('is_blocked', true);
        } elseif ($request->input('status') === 'active') {
            $query->where('is_blocked', false);
        }
        $customers = $query->latest()->paginate(15)->withQueryString();
        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $user)
    {
        abort_if($user->role !== 'customer', 404);
        $user->loadCount('orders');
        $orders = $user->orders()->with('items')->latest()->paginate(10);
        $totalSpent = $user->orders()->where('status', '!=', 'cancelled')->sum('total');
        $addresses = $user->orders()->select('shipping_address', 'city')->distinct()->get();
        return view('admin.customers.show', compact('user', 'orders', 'totalSpent', 'addresses'));
    }

    public function toggleBlock(User $user)
    {
        abort_if($user->role !== 'customer', 403);
        $user->update(['is_blocked' => ! $user->is_blocked]);
        ActivityLogger::log($user->is_blocked ? 'customer.block' : 'customer.unblock', $user->email, $user->name);
        return back()->with('success', $user->is_blocked ? 'Customer blocked.' : 'Customer unblocked.');
    }
}
