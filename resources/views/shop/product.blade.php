@extends('layouts.app')
@section('title', $product->name . ' — ShopFresh')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 grid md:grid-cols-2 gap-8">
  <div>
    <div class="bg-leaf-50 rounded-3xl flex items-center justify-center h-96 overflow-hidden">
      @if($product->imageUrl())
        <img id="gallery-main" src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
      @else
        <i data-lucide="shopping-basket" class="w-28 h-28 text-leaf-600"></i>
      @endif
    </div>
    @if($product->images->isNotEmpty())
      <div class="flex gap-2 mt-3 overflow-x-auto pb-1">
        @foreach($product->images as $image)
          <button type="button" onclick="document.getElementById('gallery-main').src='{{ $image->url() }}'" class="shrink-0 w-20 h-20 rounded-xl overflow-hidden border-2 border-transparent hover:border-leaf-500">
            <img src="{{ $image->url() }}" alt="{{ $product->name }} thumbnail" class="h-full w-full object-cover">
          </button>
        @endforeach
      </div>
    @endif
  </div>
  <div>
    <div class="text-xs uppercase tracking-widest text-leaf-600 font-bold">{{ $product->category->name }}</div>
    <h1 class="font-display text-3xl md:text-4xl font-bold mt-1">{{ $product->name }}</h1>
    <div class="mt-2 text-sm text-stone-500 flex items-center gap-3">
      <span class="flex items-center gap-1"><i data-lucide="star" class="w-4 h-4 text-sun-500 fill-current"></i>{{ $product->reviews->avg('rating') ? number_format($product->reviews->avg('rating'), 1) : 'No ratings yet' }}</span>
      <span class="flex items-center gap-1"><i data-lucide="message-square" class="w-4 h-4"></i>{{ $product->reviews->count() }} reviews</span>
    </div>
    <div class="mt-4 flex items-baseline gap-3">
      @if($product->sale_price)
        <span class="text-4xl font-black text-brand-600">{{ $currency }} {{ number_format($product->sale_price, 2) }}</span>
        <span class="text-xl text-stone-400 line-through">{{ $currency }} {{ number_format($product->price, 2) }}</span>
        <span class="bg-sun-300 text-stone-900 text-xs font-bold rounded-full px-2 py-1">SAVE {{ round((1 - $product->sale_price / $product->price) * 100) }}%</span>
      @else
        <span class="text-4xl font-black">{{ $currency }} {{ number_format($product->price, 2) }}</span>
      @endif
    </div>
    <p class="mt-4 text-stone-600 leading-relaxed">{{ $product->description }}</p>
    @if($product->specifications)
      <div class="mt-4 bg-white rounded-2xl border border-stone-100 p-4">
        <h2 class="text-xs font-bold uppercase tracking-widest text-stone-400 mb-1">Specifications</h2>
        <ul class="text-sm text-stone-600 space-y-1">@foreach(array_filter(array_map('trim', explode("\n", $product->specifications))) as $line)<li class="flex items-start gap-2"><i data-lucide="check" class="w-4 h-4 text-leaf-600 mt-0.5 shrink-0"></i>{{ $line }}</li>@endforeach</ul>
      </div>
    @endif
    @if($product->variations->isNotEmpty())
      <div class="mt-4">
        <h2 class="text-xs font-bold uppercase tracking-widest text-stone-400 mb-2">Available options</h2>
        <div class="flex flex-wrap gap-2">
          @foreach($product->variations->groupBy('type') as $type => $options)
            <div class="bg-white rounded-2xl border border-stone-100 px-4 py-2">
              <div class="text-[10px] uppercase text-stone-400">{{ $type }}</div>
              <div class="flex gap-1 mt-1">@foreach($options as $option)<span class="text-xs border border-leaf-500 text-leaf-700 rounded-full px-3 py-0.5">{{ $option->value }}{{ $option->price_delta > 0 ? ' (+' . number_format($option->price_delta, 0) . ')' : '' }}</span>@endforeach</div>
            </div>
          @endforeach
        </div>
      </div>
    @endif
    @auth
      <form action="{{ route('cart.add', $product) }}" method="POST" class="mt-6 flex items-center gap-3">@csrf
        <input type="number" name="qty" value="1" min="1" max="{{ max(1, $product->stock) }}" class="w-20 border border-stone-200 rounded-xl px-3 py-3 text-center">
        <button class="flex items-center gap-2 bg-sun-400 text-stone-950 font-bold rounded-full px-8 py-3 hover:bg-sun-300 {{ $product->stock < 1 ? 'opacity-50 cursor-not-allowed' : '' }}" @disabled($product->stock < 1)><i data-lucide="shopping-cart" class="w-5 h-5"></i>Add to cart</button>
      </form>
    @else
      <a href="{{ route('login') }}" class="mt-6 inline-flex items-center gap-2 bg-leaf-500 text-white font-bold rounded-full px-8 py-3 hover:bg-leaf-600"><i data-lucide="log-in" class="w-5 h-5"></i>Log in to add to cart</a>
    @endauth
  </div>
</div>

<div class="max-w-7xl mx-auto px-4 grid md:grid-cols-3 gap-8">
  <div class="md:col-span-2">
    <h2 class="font-display text-xl font-bold mb-3">Customer reviews</h2>
    @auth
      <form action="{{ route('product.review', $product) }}" method="POST" class="bg-white rounded-2xl border border-stone-100 p-4 mb-4">@csrf
        <div class="flex items-center gap-3">
          <label class="text-sm font-medium">Your rating</label>
          <select name="rating" class="border rounded-lg px-2 py-1 text-sm">@for($i=5;$i>=1;$i--)<option value="{{ $i }}">{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>@endfor</select>
        </div>
        <textarea name="comment" rows="2" placeholder="Share your experience..." class="mt-2 w-full border border-stone-200 rounded-xl px-3 py-2 text-sm"></textarea>
        <button class="mt-2 flex items-center gap-2 bg-leaf-500 text-white text-sm font-bold rounded-full px-5 py-2 hover:bg-leaf-600"><i data-lucide="send" class="w-4 h-4"></i>Submit review</button>
      </form>
    @else
      <p class="text-sm text-stone-500 mb-4"><a href="{{ route('login') }}" class="text-brand-600 font-semibold">Log in</a> to leave a review.</p>
    @endauth
    @forelse($product->reviews as $review)
      <div class="bg-white rounded-2xl border border-stone-100 p-4 mb-3">
        <div class="flex items-center justify-between">
          <b class="text-sm">{{ $review->user->name }}</b>
          <span class="flex items-center gap-0.5 text-sun-500">
            @for($i = 1; $i <= 5; $i++)
              <i data-lucide="star" class="w-4 h-4 {{ $i <= $review->rating ? 'fill-current' : 'text-stone-200' }}"></i>
            @endfor
          </span>
        </div>
        @if($review->comment)<p class="text-sm text-stone-600 mt-1">{{ $review->comment }}</p>@endif
      </div>
    @empty
      <p class="text-sm text-stone-500">Be the first to review this product.</p>
    @endforelse
  </div>
  <div>
    <h2 class="font-display text-xl font-bold mb-3">You may also like</h2>
    <div class="grid grid-cols-2 gap-3">@foreach($related as $product)@include('shop._card', ['product' => $product])@endforeach</div>
  </div>
</div>
@endsection
