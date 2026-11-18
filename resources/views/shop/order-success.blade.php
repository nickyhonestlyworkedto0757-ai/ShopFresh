@extends('layouts.app')
@section('title', 'Order confirmed — ShopFresh')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-16 text-center">
  <div class="w-20 h-20 bg-leaf-100 rounded-full flex items-center justify-center mx-auto"><i data-lucide="check" class="w-10 h-10 text-leaf-600"></i></div>
  <h1 class="font-display text-3xl font-bold mt-6">Thank you, {{ $order->customer_name }}!</h1>
  <p class="text-stone-500 mt-2">Your order <b>{{ $order->order_number }}</b> has been received.</p>
  <p class="text-sm text-stone-500 mt-1">Payment: <b>{{ strtoupper($order->payment_method) }}</b> · Status: <b>{{ ucfirst($order->status) }}</b></p>
  <div class="bg-white rounded-2xl border border-stone-100 p-6 mt-6 text-left">
    @foreach($order->items as $item)
      <div class="flex justify-between text-sm py-1"><span>{{ $item->quantity }} × {{ $item->product_name }}</span><span>{{ $currency }} {{ number_format($item->price * $item->quantity, 2) }}</span></div>
    @endforeach
    <div class="border-t my-2"></div>
    <div class="flex justify-between font-black"><span>Total</span><span>{{ $currency }} {{ number_format($order->total, 2) }}</span></div>
  </div>
  <a href="{{ route('catalog') }}" class="inline-flex items-center gap-2 mt-6 bg-brand-500 text-white font-bold rounded-full px-8 py-3 hover:bg-brand-600">Continue shopping<i data-lucide="arrow-right" class="w-5 h-5"></i></a>
</div>
@endsection
