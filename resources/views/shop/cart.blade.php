@extends('layouts.app')
@section('title', 'Your cart — ShopFresh')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
  <h1 class="font-display text-3xl font-bold mb-6 flex items-center gap-3"><i data-lucide="shopping-cart" class="w-8 h-8"></i>Your cart</h1>
  @if(empty($items))
    <div class="bg-white rounded-2xl border border-stone-100 p-16 text-center">
      <i data-lucide="shopping-basket" class="w-16 h-16 mx-auto text-stone-300"></i>
      <p class="mt-4 text-stone-500">Your cart is empty.</p>
      <a href="{{ route('catalog') }}" class="inline-flex items-center gap-2 mt-4 bg-brand-500 text-white font-bold rounded-full px-6 py-3 hover:bg-brand-600">Browse products<i data-lucide="arrow-right" class="w-5 h-5"></i></a>
    </div>
  @else
    <div class="grid md:grid-cols-3 gap-6">
      <div class="md:col-span-2 space-y-3">
        @foreach($items as $item)
          <div class="bg-white rounded-2xl border border-stone-100 p-4 flex items-center gap-4">
            <div class="w-20 h-20 rounded-xl bg-leaf-50 flex items-center justify-center overflow-hidden shrink-0">
              @if($item['product']->imageUrl())
                <img src="{{ $item['product']->imageUrl() }}" alt="{{ $item['product']->name }}" class="h-full w-full object-cover">
              @else
                <i data-lucide="shopping-basket" class="w-8 h-8 text-leaf-600"></i>
              @endif
            </div>
            <div class="flex-1 min-w-0">
              <a href="{{ route('product', $item['product']->slug) }}" class="font-semibold hover:text-brand-600">{{ $item['product']->name }}</a>
              <div class="text-sm text-stone-500">{{ $currency }} {{ number_format($item['price'], 2) }} each</div>
              <form action="{{ route('cart.update', $item['product']) }}" method="POST" class="mt-1 flex items-center gap-2">@csrf @method('PATCH')
                <button name="qty" value="{{ $item['qty'] - 1 }}" class="w-7 h-7 rounded-full border flex items-center justify-center hover:bg-stone-50" aria-label="Decrease"><i data-lucide="minus" class="w-4 h-4"></i></button>
                <span class="w-8 text-center font-bold">{{ $item['qty'] }}</span>
                <button name="qty" value="{{ $item['qty'] + 1 }}" class="w-7 h-7 rounded-full border flex items-center justify-center hover:bg-stone-50" aria-label="Increase"><i data-lucide="plus" class="w-4 h-4"></i></button>
              </form>
            </div>
            <div class="text-right shrink-0">
              <div class="font-black">{{ $currency }} {{ number_format($item['line'], 2) }}</div>
              <form action="{{ route('cart.remove', $item['product']) }}" method="POST">@csrf @method('DELETE')<button class="flex items-center gap-1 text-xs text-brand-600 font-semibold mt-2 hover:underline"><i data-lucide="trash-2" class="w-4 h-4"></i>Remove</button></form>
            </div>
          </div>
        @endforeach
      </div>
      <div class="bg-white rounded-2xl border border-stone-100 p-6 h-fit">
        <h2 class="font-bold text-lg">Order summary</h2>
        <div class="flex justify-between text-sm mt-3"><span>Subtotal</span><b>{{ $currency }} {{ number_format($subtotal, 2) }}</b></div>
        <div class="flex justify-between text-sm text-stone-500"><span>Delivery</span><span>Free</span></div>
        <div class="border-t my-3"></div>
        <div class="flex justify-between font-black text-xl"><span>Total</span><span>{{ $currency }} {{ number_format($subtotal, 2) }}</span></div>
        <a href="{{ route('checkout') }}" class="mt-4 flex items-center justify-center gap-2 bg-brand-500 text-white text-center font-bold rounded-full py-3 hover:bg-brand-600">Proceed to checkout<i data-lucide="arrow-right" class="w-5 h-5"></i></a>
      </div>
    </div>
  @endif
</div>
@endsection
