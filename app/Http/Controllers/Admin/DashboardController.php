<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $notCancelled = fn ($q) => $q->where('status', '!=', 'cancelled');

        $statusCounts = Order::select('status', DB::raw('COUNT(*) as count'))->groupBy('status')->pluck('count', 'status');

        $stats = [
            'revenue' => $notCancelled(Order::query())->sum('total'),
            'revenue_today' => $notCancelled(Order::whereDate('created_at', today()))->sum('total'),
            'orders' => Order::count(),
            'orders_today' => Order::whereDate('created_at', today())->count(),
            'customers' => User::where('role', 'customer')->count(),
            'products' => Product::count(),
            'pending' => $statusCounts['pending'] ?? 0,
            'processing' => $statusCounts['processing'] ?? 0,
            'shipped' => $statusCounts['shipped'] ?? 0,
            'delivered' => $statusCounts['delivered'] ?? 0,
            'low_stock' => Product::whereColumn('stock', '<=', 'low_stock_threshold')->count(),
        ];

        $lowStockProducts = Product::whereColumn('stock', '<=', 'low_stock_threshold')->orderBy('stock')->take(6)->get();
        $recentOrders = Order::latest()->take(8)->get();

        $daily = Order::select(DB::raw('DATE(created_at) as day'), DB::raw('SUM(total) as total'))
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('day')->orderBy('day')->get()->keyBy('day');
        $dailyLabels = [];
        $dailyTotals = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $dailyLabels[] = now()->subDays($i)->format('M j');
            $dailyTotals[] = (float) ($daily[$day]->total ?? 0);
        }

        $monthly = Order::select(DB::raw("strftime('%Y-%m', created_at) as month"), DB::raw('SUM(total) as total'))
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')->orderBy('month')->get()->keyBy('month');
        $monthlyLabels = [];
        $monthlyTotals = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $monthlyLabels[] = $m->format('M Y');
            $monthlyTotals[] = (float) ($monthly[$m->format('Y-m')]->total ?? 0);
        }

        $yearly = Order::select(DB::raw("strftime('%Y', created_at) as year"), DB::raw('SUM(total) as total'))
            ->where('status', '!=', 'cancelled')
            ->groupBy('year')->orderBy('year')->get();

        $bestSellers = OrderItem::select('product_id', 'product_name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(quantity * price) as revenue'))
            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('qty')
            ->take(5)->get();

        $paymentBreakdown = Order::select('payment_method', DB::raw('COUNT(*) as count'))->groupBy('payment_method')->pluck('count', 'payment_method');

        return view('admin.dashboard', compact(
            'stats', 'lowStockProducts', 'recentOrders',
            'dailyLabels', 'dailyTotals', 'monthlyLabels', 'monthlyTotals', 'yearly',
            'bestSellers', 'paymentBreakdown'
        ));
    }
}
