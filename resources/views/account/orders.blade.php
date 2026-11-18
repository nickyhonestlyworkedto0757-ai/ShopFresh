@extends('layouts.app')
@section('title', 'My orders — ShopFresh')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
  <h1 class="font-display text-3xl font-bold mb-6 flex items-center gap-3"><i data-lucide="package" class="w-8 h-8"></i>My orders</h1>
  @forelse($orders as $order)
    <a href="{{ route('account.order', $order->id) }}" class="block bg-white rounded-2xl border border-stone-100 p-5 mb-3 hover:shadow">
      <div class="flex justify-between items-center flex-wrap gap-2">
        <div><b>{{ $order->order_number }}</b><div class="text-xs text-stone-500">{{ $order->created_at->format('d M Y, H:i') }} · {{ strtoupper($order->payment_method) }}</div></div>
        <div class="text-right"><div class="font-black">{{ $currency }} {{ number_format($order->total, 2) }}</div><span class="text-xs font-bold rounded-full px-3 py-1 {{ $order->status === 'delivered' ? 'bg-leaf-100 text-leaf-700' : 'bg-sun-100 text-stone-700' }}">{{ strtoupper($order->status) }}</span></div>
      </div>
    </a>
  @empty
    <div class="text-center py-16 text-stone-400">
      <i data-lucide="package-open" class="w-14 h-14 mx-auto mb-3"></i>
      <p>No orders yet. <a href="{{ route('catalog') }}" class="text-brand-600 font-semibold">Start shopping</a></p>
    </div>
  @endforelse
  <div class="mt-4">{{ $orders->links() }}</div>
</div>
@endsection
