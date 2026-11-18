<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Services\MpesaService;
use App\Support\ActivityLogger;
use App\Support\Cart;
use App\Support\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = Cart::detailed();
        if (empty($cart['items'])) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }
        [$discount, $coupon] = $this->couponDiscount($cart['subtotal']);
        $zones = DeliveryZone::where('is_active', true)->orderBy('fee')->get();
        $defaultFee = (float) \App\Support\Settings::get('delivery_fee', 0);
        return view('shop.checkout', array_merge($cart, ['discount' => $discount, 'coupon' => $coupon, 'zones' => $zones, 'defaultFee' => $defaultFee]));
    }

    public function applyCoupon(Request $request)
    {
        $code = strtoupper(trim($request->input('code', '')));
        $cart = Cart::detailed();
        $coupon = Coupon::where('code', $code)->first();
        if (! $coupon || ! $coupon->isValid($cart['subtotal'])) {
            $min = $coupon?->min_purchase ?? 0;
            return back()->with('error', $min > 0 ? "Coupon requires a minimum purchase of KES {$min}." : 'Invalid or expired coupon.');
        }
        session(['coupon_code' => $code]);
        return back()->with('success', 'Coupon applied.');
    }

    public function removeCoupon()
    {
        session()->forget('coupon_code');
        return back();
    }

    public function placeOrder(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:50',
            'shipping_address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'payment_method' => 'required|in:cod,mpesa,card',
            'delivery_zone_id' => 'nullable|exists:delivery_zones,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $cart = Cart::detailed();
        if (empty($cart['items'])) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        [$discount, $coupon] = $this->couponDiscount($cart['subtotal']);
        $zone = ! empty($validated['delivery_zone_id']) ? DeliveryZone::find($validated['delivery_zone_id']) : null;
        $deliveryFee = $zone ? (float) $zone->fee : (float) \App\Support\Settings::get('delivery_fee', 0);
        $total = max(0, $cart['subtotal'] - $discount) + $deliveryFee;

        $order = DB::transaction(function () use ($validated, $cart, $discount, $coupon, $total, $deliveryFee, $zone, $request) {
            $order = Order::create(array_merge(collect($validated)->except('delivery_zone_id')->all(), [
                'user_id' => $request->user()->id,
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
                'subtotal' => $cart['subtotal'],
                'discount' => $discount,
                'delivery_fee' => $deliveryFee,
                'delivery_zone_id' => $zone?->id,
                'total' => $total,
                'payment_status' => $validated['payment_method'] === 'cod' ? 'pending' : 'pending',
                'status' => 'pending',
                'delivery_status' => 'pending',
                'coupon_code' => $coupon?->code,
            ]));

            foreach ($cart['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'price' => $item['price'],
                    'quantity' => $item['qty'],
                ]);
                Product::where('id', $item['product']->id)->decrement('stock', $item['qty']);
                $fresh = Product::find($item['product']->id);
                if ($fresh && $fresh->isLowStock()) {
                    Notify::admin('low_stock', 'Low stock alert', $fresh->name . ' is down to ' . $fresh->stock . ' units.', route('admin.products.index'));
                }
            }

            if ($coupon) {
                $coupon->increment('used_count');
            }

            Payment::create([
                'order_id' => $order->id,
                'method' => $validated['payment_method'],
                'amount' => $total,
                'status' => 'pending',
                'provider' => $validated['payment_method'] === 'mpesa' ? 'daraja' : ($validated['payment_method'] === 'card' ? 'card' : 'cod'),
            ]);

            return $order;
        });

        Cart::clear();

        Notify::admin('new_order', 'New order ' . $order->order_number, $order->customer_name . ' placed an order worth KES ' . number_format($total, 2), route('admin.orders.show', $order));
        \App\Support\Notifier::orderUpdate($order, 'placed');

        if ($validated['payment_method'] === 'mpesa') {
            [$ok, $message] = \App\Support\MpesaPayment::initiate($order, $validated['customer_phone']);
            return redirect()->route('order.payment', $order)->with($ok ? 'success' : 'error', $ok ? $message : 'Order placed, but the M-Pesa prompt failed: ' . $message);
        }

        if ($validated['payment_method'] === 'card') {
            return redirect()->route('order.success', $order)->with('success', 'Order placed! Card payment will be verified by our team.');
        }

        return redirect()->route('order.success', $order)->with('success', 'Order placed! Pay on delivery.');
    }

    public function success(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id && ! $request->user()->isStaff()) {
            abort(403);
        }
        $order->load('items');
        return view('shop.order-success', compact('order'));
    }

    public function paymentWaiting(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id && ! $request->user()->isStaff()) {
            abort(403);
        }
        $order->load('items', 'payment');
        return view('shop.payment', compact('order'));
    }

    public function paymentStatus(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id && ! $request->user()->isStaff()) {
            abort(403);
        }
        $order->load('payment');
        return response()->json([
            'order_number' => $order->order_number,
            'payment_status' => $order->payment_status,
            'payment_record_status' => $order->payment?->status,
            'receipt_number' => $order->payment?->receipt_number,
            'result_desc' => $order->payment?->result_desc,
            'redirect' => route('order.success', $order),
        ]);
    }

    public function paymentRetry(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }
        if ($order->payment_status === 'paid') {
            return redirect()->route('order.success', $order)->with('success', 'This order is already paid.');
        }
        $validated = $request->validate(['phone' => 'required|string|max:20']);
        [$ok, $message] = \App\Support\MpesaPayment::initiate($order, $validated['phone']);
        return redirect()->route('order.payment', $order)->with($ok ? 'success' : 'error', $message);
    }

    private function couponDiscount(float $subtotal): array
    {
        $code = session('coupon_code');
        if (! $code) {
            return [0.0, null];
        }
        $coupon = Coupon::where('code', $code)->first();
        if (! $coupon || ! $coupon->isValid($subtotal)) {
            return [0.0, null];
        }
        return [$coupon->discountFor($subtotal), $coupon];
    }
}
