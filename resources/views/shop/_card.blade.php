<div class="product-tile snap-start bg-white rounded-2xl border border-stone-100 shadow-sm overflow-hidden hover:shadow-md transition group">
  <a href="{{ route('product', $product->slug) }}" class="block relative">
    @if($product->discountPercent())<span class="absolute top-2 left-2 bg-brand-500 text-white text-[10px] font-bold rounded-full px-2 py-0.5 z-10 flex items-center gap-0.5"><i data-lucide="tag" class="w-3 h-3"></i>-{{ $product->discountPercent() }}%</span>@endif
    @if($product->is_official_store)<span class="absolute top-2 right-2 bg-sun-400 text-stone-900 text-[10px] font-bold rounded-full px-2 py-0.5 z-10 flex items-center gap-0.5"><i data-lucide="store" class="w-3 h-3"></i>Official</span>@endif
    <div class="h-28 sm:h-32 flex items-center justify-center bg-leaf-50 overflow-hidden">
      @if($product->imageUrl())
        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
      @else
        <i data-lucide="shopping-basket" class="w-12 h-12 text-leaf-600"></i>
      @endif
    </div>
  </a>
  <div class="p-3">
    <a href="{{ route('product', $product->slug) }}" class="block font-semibold leading-snug text-sm h-10 overflow-hidden group-hover:text-brand-600 line-clamp-2">{{ $product->name }}</a>
    <div class="mt-1 flex items-center gap-1 text-[11px] text-sun-500">
      @for($i = 1; $i <= 5; $i++)<i data-lucide="star" class="w-3.5 h-3.5 {{ $product->reviews()->avg('rating') >= $i ? 'fill-current' : 'text-stone-200' }}"></i>@endfor
      <span class="text-stone-400 ml-1">({{ $product->reviews_count ?? $product->reviews()->count() }})</span>
    </div>
    <div class="mt-1 flex items-baseline gap-2">
      @if($product->sale_price)
        <span class="font-black text-lg text-brand-600">{{ $currency }} {{ number_format($product->sale_price, 2) }}</span>
        <span class="text-[11px] text-stone-400 line-through">{{ number_format($product->price, 2) }}</span>
      @else
        <span class="font-black text-lg">{{ $currency }} {{ number_format($product->price, 2) }}</span>
      @endif
    </div>
    @auth
      <form action="{{ route('cart.add', $product) }}" method="POST" class="mt-2">@csrf
        <button class="w-full flex items-center justify-center gap-2 bg-sun-400 text-stone-950 text-sm font-bold rounded-full py-2 hover:bg-sun-300 {{ $product->stock < 1 ? 'opacity-50 cursor-not-allowed' : '' }}" @disabled($product->stock < 1)>
          <i data-lucide="{{ $product->stock < 1 ? 'x-circle' : 'shopping-cart' }}" class="w-4 h-4"></i>{{ $product->stock < 1 ? 'Out of stock' : 'Add to cart' }}
        </button>
      </form>
    @else
      <a href="{{ route('login') }}" class="mt-2 flex w-full items-center justify-center gap-2 bg-sun-400 text-stone-950 text-sm font-bold rounded-full py-2 hover:bg-sun-300">
        <i data-lucide="log-in" class="w-4 h-4"></i>Log in to buy
      </a>
    @endauth
  </div>
</div>
