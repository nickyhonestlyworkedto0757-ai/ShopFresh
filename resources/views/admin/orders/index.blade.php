@extends('admin.layout')
@section('title', 'Orders — Admin')
@section('content')
<h1 class="text-2xl font-bold">Orders</h1>
<form method="GET" class="flex flex-wrap gap-2 mt-3">
  <input name="q" value="{{ request('q') }}" placeholder="Order #, invoice #, customer, phone..." class="flex-1 min-w-[200px] border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 rounded-xl px-3 py-2 text-sm">
  <select name="status" class="border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 rounded-xl px-3 py-2 text-sm">
    <option value="">All statuses</option>
    @foreach(['pending','processing','shipped','delivered','cancelled'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach
  </select>
  <select name="payment_status" class="border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 rounded-xl px-3 py-2 text-sm">
    <option value="">All payments</option>
    @foreach(['pending','paid','failed','refunded'] as $s)<option value="{{ $s }}" @selected(request('payment_status') === $s)>{{ ucfirst($s) }}</option>@endforeach
  </select>
  <button class="flex items-center gap-1 bg-stone-900 dark:bg-white text-white dark:text-stone-900 rounded-xl px-4 py-2 text-sm font-semibold"><i data-lucide="search" class="w-4 h-4"></i>Filter</button>
</form>
<div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden mt-4 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3">Order</th><th class="p-3">Customer</th><th class="p-3">Total</th><th class="p-3">Payment</th><th class="p-3">Status</th><th class="p-3">Delivery</th><th></th></tr></thead>
    <tbody>
      @forelse($orders as $order)
        <tr class="border-t border-stone-100 dark:border-stone-800">
          <td class="p-3 font-semibold">{{ $order->order_number }}<div class="text-[10px] text-stone-400 font-normal">{{ $order->created_at->format('d M H:i') }} · {{ $order->invoice_number }}</div></td>
          <td class="p-3">{{ $order->customer_name }}<div class="text-[10px] text-stone-400">{{ $order->customer_phone }}</div></td>
          <td class="p-3">{{ $currency }} {{ number_format($order->total, 2) }}</td>
          <td class="p-3">{{ strtoupper($order->payment_method) }}<div class="text-[10px] text-stone-400">{{ $order->payment_status }}</div></td>
          <td class="p-3">{{ ucfirst($order->status) }}</td>
          <td class="p-3">{{ str_replace('_', ' ', ucfirst($order->delivery_status)) }}</td>
          <td class="p-3 text-right"><a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center gap-1 text-brand-600 font-semibold">Manage<i data-lucide="arrow-right" class="w-4 h-4"></i></a></td>
        </tr>
      @empty
        <tr><td colspan="7" class="p-6 text-center text-stone-400">No orders found.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
