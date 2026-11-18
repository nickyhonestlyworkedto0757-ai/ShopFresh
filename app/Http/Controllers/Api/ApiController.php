<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Support\Notify;
use App\Support\Settings;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    public function categories()
    {
        return response()->json(Category::with('subcategories')->withCount('products')->whereNull('parent_id')->orderBy('sort_order')->orderBy('name')->get());
    }

    public function homepage()
    {
        $sections = \App\Models\HomepageSection::where('is_active', true)->orderBy('sort_order')->get()->map(function ($section) {
            if ($section->mode === 'categories') {
                return ['section' => $section->toArray(), 'categories' => Category::orderBy('sort_order')->orderBy('name')->take(12)->get()];
            }
            $products = $section->products();
            return $products->isNotEmpty() ? ['section' => $section->toArray(), 'products' => $products->map(fn ($p) => $this->serializeProduct($p))] : null;
        })->filter()->values();
        return response()->json($sections);
    }

    public function banners()
    {
        return response()->json(
            Banner::where('is_active', true)->orderBy('sort_order')->orderBy('id', 'desc')->get()
                ->map(fn ($b) => ['id' => $b->id, 'title' => $b->title, 'subtitle' => $b->subtitle, 'image_url' => $b->imageUrl(), 'link_url' => $b->link_url])
        );
    }

    public function products(Request $request)
    {
        $query = Product::with('category')->active();
        if ($search = $request->query('q')) {
            $query->where(fn ($w) => $w->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"));
        }
        if ($category = $request->query('category')) {
            $query->whereHas('category', fn ($w) => $w->where('slug', $category));
        }
        $page = $query->latest()->paginate(20);
        $page->getCollection()->transform(fn ($p) => $this->serializeProduct($p));
        return response()->json($page);
    }

    public function product(string $slug)
    {
        $product = Product::with(['category', 'reviews.user', 'images', 'variations'])->active()->where('slug', $slug)->firstOrFail();
        return response()->json($this->serializeProduct($product, true));
    }

    private function serializeProduct(Product $p, bool $detail = false): array
    {
        $data = $p->toArray();
        $data['image_url'] = $p->imageUrl();
        $data['gallery'] = $p->images->map->url()->all();
        if ($detail) {
            $data['average_rating'] = $p->averageRating();
        }
        return $data;
    }

    public function deliveryZones()
    {
        return response()->json(DeliveryZone::where('is_active', true)->orderBy('fee')->get(['id', 'name', 'fee']));
    }

    public function storeSettings()
    {
        return response()->json([
            'store_name' => Settings::get('store_name'),
            'store_logo' => Settings::get('store_logo') ? asset('storage/' . Settings::get('store_logo')) : null,
            'store_email' => Settings::get('store_email'),
            'store_phone' => Settings::get('store_phone'),
            'currency' => Settings::get('currency'),
            'delivery_fee' => (float) Settings::get('delivery_fee', 0),
            'social_facebook' => Settings::get('social_facebook'),
            'social_instagram' => Settings::get('social_instagram'),
            'social_twitter' => Settings::get('social_twitter'),
            'social_tiktok' => Settings::get('social_tiktok'),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:50',
        ]);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'role' => 'customer',
            'is_admin' => false,
            'api_token' => Str::random(60),
        ]);
        Notify::admin('new_customer', 'New customer registered', $user->name . ' (' . $user->email . ')');
        \App\Models\NotificationPreference::for($user);
        return response()->json(['token' => $user->api_token, 'user' => $user->only('id', 'name', 'email')], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required']);
        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        if ($user->is_blocked) {
            return response()->json(['message' => 'This account has been suspended.'], 403);
        }
        if (! $user->api_token) {
            $user->api_token = Str::random(60);
            $user->save();
        }
        return response()->json(['token' => $user->api_token, 'user' => $user->only('id', 'name', 'email', 'is_admin', 'role')]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->only('id', 'name', 'email', 'phone', 'is_admin', 'role'));
    }

    public function myOrders(Request $request)
    {
        return response()->json($request->user()->orders()->with('items')->latest()->get());
    }

    public function placeOrder(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string|max:50',
            'shipping_address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'payment_method' => 'required|in:cod,mpesa,card',
            'delivery_zone_id' => 'nullable|exists:delivery_zones,id',
            'coupon_code' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $products = Product::active()->whereIn('id', collect($data['items'])->pluck('product_id'))->get()->keyBy('id');
        if ($products->count() !== count($data['items'])) {
            return response()->json(['message' => 'One or more products are no longer available.'], 422);
        }
        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $subtotal += $products[$item['product_id']]->effectivePrice() * $item['quantity'];
        }

        $discount = 0;
        $coupon = null;
        if (! empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', strtoupper($data['coupon_code']))->first();
            if ($coupon && $coupon->isValid($subtotal)) {
                $discount = $coupon->discountFor($subtotal);
            }
        }

        $zone = ! empty($data['delivery_zone_id']) ? DeliveryZone::find($data['delivery_zone_id']) : null;
        $deliveryFee = $zone ? (float) $zone->fee : (float) Settings::get('delivery_fee', 0);
        $total = max(0, $subtotal - $discount) + $deliveryFee;

        $order = DB::transaction(function () use ($data, $products, $subtotal, $discount, $coupon, $total, $deliveryFee, $zone, $request) {
            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'shipping_address' => $data['shipping_address'],
                'city' => $data['city'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_fee' => $deliveryFee,
                'delivery_zone_id' => $zone?->id,
                'total' => $total,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'status' => 'pending',
                'delivery_status' => 'pending',
                'coupon_code' => $coupon?->code,
            ]);
            foreach ($data['items'] as $item) {
                $p = $products[$item['product_id']];
                $order->items()->create([
                    'product_id' => $p->id,
                    'product_name' => $p->name,
                    'price' => $p->effectivePrice(),
                    'quantity' => $item['quantity'],
                ]);
                Product::where('id', $p->id)->decrement('stock', $item['quantity']);
                $fresh = Product::find($p->id);
                if ($fresh && $fresh->isLowStock()) {
                    Notify::admin('low_stock', 'Low stock alert', $fresh->name . ' is down to ' . $fresh->stock . ' units.');
                }
            }
            if ($coupon) {
                $coupon->increment('used_count');
            }
            Payment::create([
                'order_id' => $order->id,
                'method' => $data['payment_method'],
                'amount' => $total,
                'status' => 'pending',
                'provider' => $data['payment_method'] === 'mpesa' ? 'daraja' : ($data['payment_method'] === 'card' ? 'card' : 'cod'),
            ]);
            return $order->load('items');
        });

        Notify::admin('new_order', 'New order ' . $order->order_number, $order->customer_name . ' placed an order worth KES ' . number_format($total, 2), '/admin/orders/' . $order->id);
        \App\Support\Notifier::orderUpdate($order, 'placed');

        return response()->json(array_merge($order->toArray(), [
            'payment_method' => $data['payment_method'],
            'requires_mpesa_payment' => $data['payment_method'] === 'mpesa',
        ]), 201);
    }

    /** Initiate (or retry) an STK push for one of the user's orders. */
    public function myPreferences(Request $request)
    {
        $prefs = \App\Models\NotificationPreference::for($request->user());
        return response()->json($prefs);
    }

    public function updatePreferences(Request $request)
    {
        $data = $request->validate([
            'email_order_updates' => 'boolean',
            'sms_order_updates' => 'boolean',
            'email_new_products' => 'boolean',
            'sms_new_products' => 'boolean',
            'email_promotions' => 'boolean',
            'sms_promotions' => 'boolean',
        ]);
        $prefs = \App\Models\NotificationPreference::for($request->user());
        $prefs->fill($data);
        $prefs->save();
        return response()->json($prefs);
    }

    public function mpesaStkPush(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'phone' => 'required|string|max:20',
        ]);
        $order = $request->user()->orders()->findOrFail($data['order_id']);
        if ($order->payment_method !== 'mpesa') {
            return response()->json(['message' => 'This order does not use M-Pesa.'], 422);
        }
        [$ok, $message, $payment] = \App\Support\MpesaPayment::initiate($order, $data['phone']);
        return response()->json(['ok' => $ok, 'message' => $message, 'order_number' => $order->order_number, 'amount' => $order->total], $ok ? 200 : 422);
    }

    /** Poll payment status for one of the user's orders (never trust client-side state). */
    public function orderPaymentStatus(Request $request, Order $order)
    {
        abort_if($order->user_id !== $request->user()->id, 403);
        $order->load('payment');
        return response()->json([
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'payment_record_status' => $order->payment?->status,
            'receipt_number' => $order->payment?->receipt_number,
            'result_desc' => $order->payment?->result_desc,
            'total' => $order->total,
        ]);
    }

    /** Daraja STK callback — public endpoint, matches the transaction ID we stored. */
    public function mpesaCallback(Request $request)
    {
        $result = \App\Support\MpesaPayment::handleCallback($request->all());
        return response()->json($result);
    }
}
