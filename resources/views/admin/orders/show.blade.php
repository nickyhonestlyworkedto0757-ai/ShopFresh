@extends('admin.layout')
@section('title', 'Order ' . $order->order_number)
@section('content')
<div class="flex items-center justify-between flex-wrap gap-2">
  <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1 text-sm text-brand-600 font-semibold"><i data-lucide="arrow-left" class="w-4 h-4"></i>Orders</a>
  <div class="flex gap-2">
    <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="inline-flex items-center gap-1 text-sm border border-stone-300 dark:border-stone-700 rounded-full px-4 py-2 font-semibold"><i data-lucide="printer" class="w-4 h-4"></i>Invoice</a>
    <a href="{{ route('admin.orders.invoice.pdf', $order) }}" class="inline-flex items-center gap-1 text-sm border border-stone-300 dark:border-stone-700 rounded-full px-4 py-2 font-semibold"><i data-lucide="file-text" class="w-4 h-4"></i>PDF</a>
  </div>
</div>

<div class="grid lg:grid-cols-3 gap-4 mt-3">
  <div class="lg:col-span-2 space-y-4">
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6">
      <div class="flex items-center justify-between flex-wrap gap-2">
        <div><h1 class="text-2xl font-bold">{{ $order->order_number }}</h1><p class="text-xs text-stone-500">{{ $order->created_at->format('d M Y, H:i') }} · {{ $order->invoice_number }}</p></div>
        <span class="text-xs font-bold uppercase rounded-full px-3 py-1 {{ $order->status === 'delivered' ? 'bg-leaf-100 text-leaf-700' : ($order->status === 'cancelled' ? 'bg-brand-50 text-brand-600' : 'bg-sun-100 text-stone-700') }}">{{ $order->status }}</span>
      </div>
      <div class="border-t border-stone-100 dark:border-stone-800 my-4"></div>
      <table class="w-full text-sm">
        <thead class="text-left text-stone-500"><tr><th class="py-1">Item</th><th class="py-1">Qty</th><th class="py-1 text-right">Total</th></tr></thead>
        <tbody>@foreach($order->items as $item)<tr class="border-t border-stone-100 dark:border-stone-800"><td class="py-2">{{ $item->product_name }}</td><td class="py-2">{{ $item->quantity }}</td><td class="py-2 text-right">{{ $currency }} {{ number_format($item->price * $item->quantity, 2) }}</td></tr>@endforeach</tbody>
      </table>
      <div class="border-t border-stone-100 dark:border-stone-800 my-4"></div>
      <div class="flex justify-between text-sm"><span>Subtotal</span><span>{{ $currency }} {{ number_format($order->subtotal, 2) }}</span></div>
      <div class="flex justify-between text-sm text-leaf-700 dark:text-leaf-100"><span>Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</span><span>− {{ $currency }} {{ number_format($order->discount, 2) }}</span></div>
      <div class="flex justify-between text-sm"><span>Delivery @if($order->deliveryZone)({{ $order->deliveryZone->name }})@endif</span><span>{{ $currency }} {{ number_format($order->delivery_fee, 2) }}</span></div>
      <div class="flex justify-between font-black text-xl mt-2"><span>Total</span><span>{{ $currency }} {{ number_format($order->total, 2) }}</span></div>
      @if($order->refund_amount > 0)<div class="flex justify-between text-sm text-brand-600 mt-1"><span>Refunded ({{ $order->refund_status }})</span><span>{{ $currency }} {{ number_format($order->refund_amount, 2) }}</span></div>@endif
      <div class="mt-4 text-sm text-stone-600 dark:text-stone-300">
        <b>{{ $order->customer_name }}</b> · {{ $order->customer_phone }} · {{ $order->customer_email }}<br>
        {{ $order->shipping_address }}, {{ $order->city }}
        @if($order->notes)<br><i>{{ $order->notes }}</i>@endif
      </div>
    </div>

    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6">
      <h2 class="font-bold flex items-center gap-2 mb-3"><i data-lucide="credit-card" class="w-5 h-5"></i>Payment</h2>
      <div class="text-sm space-y-1">
        <div class="flex justify-between"><span class="text-stone-500">Method</span><b>{{ strtoupper($order->payment_method) }}</b></div>
        <div class="flex justify-between"><span class="text-stone-500">Status</span><b>{{ ucfirst($order->payment_status) }}</b></div>
        @if($order->payment)
          <div class="flex justify-between"><span class="text-stone-500">Transaction</span><b class="font-mono">{{ $order->payment->transaction_id ?? '—' }}</b></div>
          <div class="flex justify-between"><span class="text-stone-500">Verified</span><b>{{ $order->payment->verified_at?->format('d M Y, H:i') ?? 'Not yet' }}</b></div>
        @endif
      </div>
    </div>

    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6">
      <h2 class="font-bold flex items-center gap-2 mb-3"><i data-lucide="send" class="w-5 h-5"></i>Notify customer</h2>
      <form action="{{ route('admin.orders.notify', $order) }}" method="POST" class="flex gap-2">@csrf
        <input name="message" required placeholder="e.g. Your order is out for delivery" class="flex-1 border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm">
        <button class="flex items-center gap-1 bg-stone-900 dark:bg-white text-white dark:text-stone-900 rounded-xl px-4 text-sm font-semibold"><i data-lucide="mail" class="w-4 h-4"></i>Send</button>
      </form>
      <p class="text-[11px] text-stone-400 mt-2">Sends an email to {{ $order->customer_email }}.</p>
    </div>
  </div>

  <div class="space-y-4">
    <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6 space-y-3">@csrf @method('PATCH')
      <h2 class="font-bold flex items-center gap-2"><i data-lucide="settings" class="w-5 h-5"></i>Update status</h2>
      <div><label class="text-xs font-semibold text-stone-500">Order status</label><select name="status" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2">@foreach(['pending','processing','shipped','delivered','cancelled'] as $s)<option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
      <div><label class="text-xs font-semibold text-stone-500">Payment status</label><select name="payment_status" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2">@foreach(['pending','paid','failed','refunded'] as $s)<option value="{{ $s }}" @selected($order->payment_status === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
      <div><label class="text-xs font-semibold text-stone-500">Delivery status</label><select name="delivery_status" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2">@foreach(['pending','assigned','out_for_delivery','delivered','failed'] as $s)<option value="{{ $s }}" @selected($order->delivery_status === $s)>{{ str_replace('_', ' ', ucfirst($s)) }}</option>@endforeach</select></div>
      <button class="w-full flex items-center justify-center gap-2 bg-leaf-500 text-white font-bold rounded-full py-2"><i data-lucide="save" class="w-4 h-4"></i>Save changes</button>
    </form>

    @if(Auth::guard('admin')->user()->hasPermission('delivery'))
    <form action="{{ route('admin.orders.assign', $order) }}" method="POST" class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6 space-y-3">@csrf @method('PATCH')
      <h2 class="font-bold flex items-center gap-2"><i data-lucide="truck" class="w-5 h-5"></i>Delivery assignment</h2>
      <select name="assigned_to" class="w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm">
        <option value="">— Not assigned —</option>
        @foreach($personnel as $person)<option value="{{ $person->id }}" @selected($order->assigned_to === $person->id)>{{ $person->name }} ({{ str_replace('_', ' ', $person->role) }})</option>@endforeach
      </select>
      <button class="w-full flex items-center justify-center gap-2 border border-leaf-500 text-leaf-700 dark:text-leaf-100 font-bold rounded-full py-2"><i data-lucide="user-check" class="w-4 h-4"></i>Assign for delivery</button>
    </form>
    @endif

    @if(Auth::guard('admin')->user()->hasPermission('payments'))
    <form action="{{ route('admin.orders.refund', $order) }}" method="POST" class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6 space-y-3">@csrf
      <h2 class="font-bold flex items-center gap-2"><i data-lucide="undo-2" class="w-5 h-5"></i>Process refund</h2>
      <div><label class="text-xs font-semibold text-stone-500">Amount to refund ({{ $currency }})</label><input type="number" step="0.01" min="0.01" max="{{ $order->total }}" name="refund_amount" required class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
      <input name="refund_reason" placeholder="Reason (optional)" class="w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm">
      <button class="w-full flex items-center justify-center gap-2 bg-brand-500 text-white font-bold rounded-full py-2 hover:bg-brand-600"><i data-lucide="undo-2" class="w-4 h-4"></i>Record refund</button>
    </form>
    @endif
  </div>
</div>
@endsection
