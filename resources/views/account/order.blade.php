@extends('layouts.app')
@section('title', 'Order ' . $order->order_number)
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
  <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-1 text-sm text-brand-600 font-semibold"><i data-lucide="arrow-left" class="w-4 h-4"></i>Back to orders</a>
  <div class="bg-white rounded-2xl border border-stone-100 p-6 mt-3">
    <div class="flex justify-between"><h1 class="font-display text-2xl font-bold">{{ $order->order_number }}</h1><span class="text-xs font-bold rounded-full px-3 py-1 bg-sun-100 h-fit">{{ strtoupper($order->status) }}</span></div>
    <p class="text-xs text-stone-500 mt-1">{{ $order->created_at->format('d M Y, H:i') }} · {{ strtoupper($order->payment_method) }} · {{ strtoupper($order->payment_status) }}</p>
    <div class="border-t my-4"></div>
    @foreach($order->items as $item)
      <div class="flex justify-between text-sm py-1"><span>{{ $item->quantity }} × {{ $item->product_name }}</span><span>{{ $currency }} {{ number_format($item->price * $item->quantity, 2) }}</span></div>
    @endforeach
    <div class="border-t my-4"></div>
    <div class="flex justify-between text-sm"><span>Subtotal</span><span>{{ number_format($order->subtotal, 2) }}</span></div>
    <div class="flex justify-between text-sm text-leaf-600"><span>Discount</span><span>− {{ number_format($order->discount, 2) }}</span></div>
    <div class="flex justify-between font-black text-xl mt-1"><span>Total</span><span>{{ $currency }} {{ number_format($order->total, 2) }}</span></div>
    <div class="mt-4 text-sm text-stone-600"><b>Deliver to:</b> {{ $order->shipping_address }}, {{ $order->city }} · {{ $order->customer_phone }}</div>
  </div>
</div>
@endsection
