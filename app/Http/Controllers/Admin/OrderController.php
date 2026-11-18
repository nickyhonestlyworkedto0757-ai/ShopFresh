<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\Notify;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::latest();
        if ($request->user()->role === 'delivery') {
            $query->where('assigned_to', $request->user()->id);
        }
        if ($q = trim((string) $request->input('q'))) {
            $query->where(fn ($w) => $w->where('order_number', 'like', "%{$q}%")
                ->orWhere('invoice_number', 'like', "%{$q}%")
                ->orWhere('customer_name', 'like', "%{$q}%")
                ->orWhere('customer_email', 'like', "%{$q}%")
                ->orWhere('customer_phone', 'like', "%{$q}%"));
        }
        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($payment = $request->string('payment_status')->toString()) {
            $query->where('payment_status', $payment);
        }
        $orders = $query->paginate(15)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items', 'payment', 'deliveryZone', 'assignee']);
        $personnel = User::whereIn('role', ['delivery', 'order_manager', 'super_admin'])->orderBy('name')->get();
        return view('admin.orders.show', compact('order', 'personnel'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'delivery_status' => 'required|in:pending,assigned,out_for_delivery,delivered,failed',
        ]);
        if (! $request->user()->hasPermission('payments')) {
            $validated['payment_status'] = $order->payment_status;
        }
        $wasCancelled = $order->status !== 'cancelled' && $validated['status'] === 'cancelled';
        $oldStatus = $order->status;
        $oldDelivery = $order->delivery_status;
        $order->update($validated);
        if ($validated['payment_status'] === 'paid' && $order->payment) {
            $order->payment->update(['status' => 'paid', 'verified_at' => now()]);
        }
        if ($oldStatus !== $order->status) {
            if ($order->status === 'cancelled') {
                \App\Support\Notifier::orderUpdate($order, 'cancelled');
            } elseif ($order->status === 'processing') {
                \App\Support\Notifier::orderUpdate($order, 'processing');
            } elseif ($order->status === 'shipped') {
                \App\Support\Notifier::orderUpdate($order, 'shipped');
            } elseif ($order->status === 'delivered') {
                \App\Support\Notifier::orderUpdate($order, 'delivered');
            }
        }
        if ($validated['delivery_status'] === 'out_for_delivery' && $oldDelivery !== 'out_for_delivery') {
            \App\Support\Notifier::orderUpdate($order, 'out_for_delivery');
        }
        if ($wasCancelled) {
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    \App\Models\Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                }
            }
        }
        ActivityLogger::log('order.status', $order->order_number, 'Status: ' . $validated['status'] . ', delivery: ' . $validated['delivery_status']);
        return back()->with('success', 'Order updated.');
    }

    public function assign(Request $request, Order $order)
    {
        $validated = $request->validate(['assigned_to' => 'nullable|exists:users,id']);
        $order->update(['assigned_to' => $validated['assigned_to'], 'delivery_status' => $validated['assigned_to'] ? 'assigned' : 'pending']);
        ActivityLogger::log('order.assign', $order->order_number, $validated['assigned_to'] ? 'Assigned to user #' . $validated['assigned_to'] : 'Unassigned');
        return back()->with('success', 'Delivery assignment updated.');
    }

    public function refund(Request $request, Order $order)
    {
        $validated = $request->validate(['refund_amount' => 'required|numeric|min:0.01', 'refund_reason' => 'nullable|string|max:500']);
        if ($validated['refund_amount'] > $order->total) {
            return back()->withErrors(['refund_amount' => 'Refund cannot exceed the order total.']);
        }
        $order->update([
            'refund_amount' => $validated['refund_amount'],
            'refund_status' => 'refunded',
            'payment_status' => 'refunded',
        ]);
        $order->payment?->update(['status' => 'refunded']);
        ActivityLogger::log('order.refund', $order->order_number, 'Refunded KES ' . $validated['refund_amount'] . ($validated['refund_reason'] ? ' — ' . $validated['refund_reason'] : ''));
        return back()->with('success', 'Refund recorded.');
    }

    public function notify(Request $request, Order $order)
    {
        $validated = $request->validate(['message' => 'required|string|max:500']);
        \Illuminate\Support\Facades\Mail::raw($validated['message'] . "\n\nYour order " . $order->order_number . " — ShopFresh", function ($mail) use ($order) {
            $mail->to($order->customer_email, $order->customer_name)->subject('Update on your order ' . $order->order_number);
        });
        ActivityLogger::log('order.notify', $order->order_number, $validated['message']);
        return back()->with('success', 'Notification sent to ' . $order->customer_email . ' (and logged).');
    }

    public function invoice(Order $order)
    {
        $order->load('items');
        return view('admin.orders.invoice', compact('order'));
    }

    public function invoicePdf(Order $order)
    {
        $order->load('items');
        $pdf = Pdf::loadView('admin.orders.invoice', ['order' => $order, 'pdf' => true]);
        return $pdf->download($order->invoice_number . '.pdf');
    }
}
