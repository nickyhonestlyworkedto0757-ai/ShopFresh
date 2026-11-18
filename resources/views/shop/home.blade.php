@extends('layouts.app')
@section('content')
<style>
  .product-viewport { overflow: hidden; }
  .product-track { display: flex; gap: 1rem; transition: transform .35s ease; will-change: transform; }
  .product-tile { flex: 0 0 calc((100% - 1rem) / 2); }
  @media (min-width: 640px) { .product-tile { flex-basis: calc((100% - 2rem) / 3); } }
  @media (min-width: 1024px) { .product-tile { flex-basis: calc((100% - 3rem) / 4); } }
  @media (min-width: 1280px) { .product-tile { flex-basis: calc((100% - 5rem) / 6); } }
  .section-arrow[disabled] { opacity: .35; cursor: not-allowed; }
</style>

@if($banners->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 pt-4">
  <div id="banner-slider" class="relative h-[150px] sm:h-[200px] md:h-[260px] lg:h-[280px] rounded-2xl md:rounded-3xl overflow-hidden shadow-md select-none">
    @foreach($banners as $i => $banner)
      <a href="{{ $banner->link_url ?: route('catalog') }}" class="banner-slide absolute inset-0 transition-opacity duration-500 {{ $i === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0 hidden' }}" data-index="{{ $i }}" aria-label="{{ $banner->title ?? 'Store promotion' }}">
        <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->title }}" class="absolute inset-0 w-full h-full object-cover object-center">
        @if($banner->title || $banner->subtitle)<div class="absolute inset-0 bg-gradient-to-r from-stone-900/70 via-stone-900/25 to-transparent"></div>@endif
        @if($banner->title || $banner->subtitle || $banner->link_url)
        <div class="absolute inset-0 flex flex-col justify-center px-4 sm:px-6 md:px-10 max-w-[70%] sm:max-w-md">
          @if($banner->title)<h2 class="font-display text-lg sm:text-2xl md:text-3xl font-bold text-white leading-snug drop-shadow">{{ $banner->title }}</h2>@endif
          @if($banner->subtitle)<p class="text-sun-300 font-semibold mt-1 text-[11px] sm:text-sm drop-shadow">{{ $banner->subtitle }}</p>@endif
          @if($banner->title || $banner->subtitle || $banner->link_url)<span class="mt-2 sm:mt-3 inline-flex w-fit items-center gap-1 sm:gap-2 bg-white text-stone-900 font-bold rounded-full px-3 py-1 sm:px-5 sm:py-2 text-[11px] sm:text-sm hover:bg-sun-100">Shop now<i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i></span>@endif
        </div>
        @endif
      </a>
    @endforeach
    @if($banners->count() > 1)
      <button id="banner-prev" class="absolute left-3 top-1/2 -translate-y-1/2 z-20 bg-white/80 hover:bg-white rounded-full p-2 shadow" aria-label="Previous banner"><i data-lucide="chevron-left" class="w-5 h-5"></i></button>
      <button id="banner-next" class="absolute right-3 top-1/2 -translate-y-1/2 z-20 bg-white/80 hover:bg-white rounded-full p-2 shadow" aria-label="Next banner"><i data-lucide="chevron-right" class="w-5 h-5"></i></button>
      <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-20 flex gap-2">
        @foreach($banners as $i => $banner)
          <button class="banner-dot w-2.5 h-2.5 rounded-full {{ $i === 0 ? 'bg-white' : 'bg-white/50' }}" data-index="{{ $i }}" aria-label="Go to banner {{ $i + 1 }}"></button>
        @endforeach
      </div>
    @endif
  </div>
</section>
<script>
(function () {
  const slides = document.querySelectorAll('#banner-slider .banner-slide');
  if (slides.length < 2) return;
  const dots = document.querySelectorAll('#banner-slider .banner-dot');
  let index = 0, timer;
  const show = (n) => {
    index = (n + slides.length) % slides.length;
    slides.forEach((s, i) => {
      s.classList.toggle('hidden', i !== index);
      s.classList.toggle('opacity-100', i === index);
      s.classList.toggle('opacity-0', i !== index);
      s.classList.toggle('z-10', i === index);
      s.classList.toggle('z-0', i !== index);
    });
    dots.forEach((d, i) => { d.classList.toggle('bg-white', i === index); d.classList.toggle('bg-white/50', i !== index); });
  };
  const reset = () => { clearInterval(timer); timer = setInterval(() => show(index + 1), 5000); };
  document.getElementById('banner-next')?.addEventListener('click', () => { show(index + 1); reset(); });
  document.getElementById('banner-prev')?.addEventListener('click', () => { show(index - 1); reset(); });
  dots.forEach(d => d.addEventListener('click', () => { show(+d.dataset.index); reset(); }));
  reset();
})();
</script>
@endif

<section class="max-w-7xl mx-auto px-4 mt-4">
  <div class="bg-red-700 rounded-2xl shadow-lg grid grid-cols-2 md:grid-cols-4 divide-x divide-stone-100/20 text-center py-2 md:py-3 text-[10px] sm:text-xs text-white">
    <div class="px-2 flex flex-col items-center gap-1"><i data-lucide="truck" class="w-6 h-6 text-sun-400"></i><b>Same-day delivery</b></div>
    <div class="px-2 flex flex-col items-center gap-1"><i data-lucide="sprout" class="w-6 h-6 text-sun-400"></i><b>Farm sourced</b></div>
    <div class="px-2 flex flex-col items-center gap-1"><i data-lucide="lock" class="w-6 h-6 text-sun-400"></i><b>Secure payments</b></div>
    <div class="px-2 flex flex-col items-center gap-1"><i data-lucide="undo-2" class="w-6 h-6 text-sun-400"></i><b>Easy returns</b></div>
  </div>
</section>

@foreach($sections as $entry)
  @php
    $section = $entry['section'];
    $products = $entry['products'] ?? null;
  @endphp

  @if($section->mode === 'promo' && $section->value === 'extra20')
    <section class="max-w-7xl mx-auto px-4 mt-4">
      <a href="{{ route('catalog', ['sort' => 'price_asc']) }}" class="flex items-center justify-between gap-3 bg-gradient-to-r from-leaf-600 to-leaf-700 text-white rounded-2xl px-5 sm:px-8 py-3 sm:py-4 shadow hover:shadow-lg transition">
        <div class="flex items-center gap-3">
          <span class="bg-sun-400 text-stone-950 text-[10px] font-black uppercase rounded-full px-2 py-0.5">Promo</span>
          <span class="font-display font-bold text-base sm:text-2xl">Get extra 20% off at checkout</span>
        </div>
        <span class="flex items-center gap-1 bg-white text-leaf-700 font-bold rounded-full px-4 py-2 text-xs sm:text-sm">Shop deals<i data-lucide="arrow-right" class="w-4 h-4"></i></span>
      </a>
    </section>
  @elseif($section->mode === 'promo' && $section->value === 'cart4')
    <section class="max-w-7xl mx-auto px-4 mt-4">
      <a href="{{ route('catalog') }}" class="flex items-center justify-between gap-3 bg-stone-950 text-white rounded-2xl px-5 sm:px-8 py-3 sm:py-4 shadow hover:shadow-lg transition">
        <div class="flex items-center gap-3">
          <span class="bg-sun-400 text-stone-950 text-[10px] font-black uppercase rounded-full px-2 py-0.5">Challenge</span>
          <span class="font-display font-bold text-base sm:text-2xl">Add 4 items to cart</span>
        </div>
        <span class="flex items-center gap-1 bg-white text-stone-950 font-bold rounded-full px-4 py-2 text-xs sm:text-sm">Start filling<i data-lucide="arrow-right" class="w-4 h-4"></i></span>
      </a>
    </section>
  @elseif($section->mode === 'categories')
    <section class="max-w-7xl mx-auto px-4 mt-4">
      <div class="flex items-center justify-between mb-2">
        <h2 class="font-display text-lg sm:text-2xl font-bold">{{ $section->title }}</h2>
      </div>
      <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-2">
        @foreach($categories as $cat)
          <a href="{{ route('catalog', ['category' => $cat->slug]) }}" class="flex flex-col items-center gap-1 bg-white rounded-xl border border-stone-100 p-2 hover:shadow hover:-translate-y-0.5 transition text-center">
            <i data-lucide="tag" class="w-6 h-6 text-leaf-600"></i>
            <span class="text-[9px] sm:text-[10px] font-medium leading-tight line-clamp-2">{{ $cat->name }}</span>
          </a>
        @endforeach
      </div>
    </section>
    @else
      @php $isRecent = in_array($section->key, ['recent', 'newarrivals']); $isFlash = in_array($section->key, ['flash', 'recent', 'newarrivals']); $isTopselling = $section->key === 'topselling'; $isSponsored = $section->key === 'sponsored'; @endphp
      <section class="max-w-7xl mx-auto px-4 mt-2 marketplace-section {{ $isTopselling || $isFlash || $isSponsored ? 'rounded-xl p-1 sm:p-1' : '' }}" style="background-color: {{ $isTopselling ? '#535357' : ($isSponsored ? '#000000' : ($isFlash ? ($isRecent ? '#BE1D3E' : '#1B8A4C') : 'transparent')) }}" data-section-key="{{ $section->key }}">
      <div class="flex items-end justify-between gap-3 mb-0.5">
        <div class="min-w-0">
          <h2 class="font-display text-xs sm:text-base font-bold leading-none {{ $isFlash || $isTopselling || $isSponsored ? 'text-white' : '' }}">{{ $section->title }}</h2>
          @if($section->subtitle)<p class="text-[10px] sm:text-xs {{ $isFlash || $isTopselling || $isSponsored ? 'text-white/80' : 'text-stone-500' }}">{{ $section->subtitle }}</p>@endif
          @if($section->value === 'flash_sale' && $flashEndsAt)
            <div class="mt-1 inline-flex items-center gap-1 bg-white rounded-full pl-2 pr-3 py-0.5 text-[10px] sm:text-xs font-bold text-brand-600" data-countdown="{{ $flashEndsAt->toIso8601String() }}">
              <i data-lucide="timer" class="w-4 h-4"></i>Ends in <span class="countdown-clock font-mono text-stone-900">--:--:--</span>
            </div>
          @endif
        </div>
        <a href="{{ $section->sectionUrl() }}" class="shrink-0 flex items-center gap-1 text-xs sm:text-sm font-bold {{ $isFlash || $isTopselling || $isSponsored ? 'bg-white text-brand-600 rounded-full px-4 py-1.5 hover:bg-sun-100' : 'text-brand-600 hover:underline' }}">See all<i data-lucide="chevron-right" class="w-4 h-4"></i></a>
      </div>

      <div class="relative">
        <button type="button" class="section-arrow hidden lg:flex absolute -left-3 top-1/2 -translate-y-1/2 z-10 bg-white shadow-lg border border-stone-200 rounded-full p-2 hover:bg-stone-50" data-dir="-1" aria-label="Scroll left"><i data-lucide="chevron-left" class="w-5 h-5"></i></button>
        <div class="product-viewport">
          <div class="product-track">
            @foreach($products as $product)
              @include('shop._card', ['product' => $product])
            @endforeach
          </div>
        </div>
        <button type="button" class="section-arrow hidden lg:flex absolute -right-3 top-1/2 -translate-y-1/2 z-10 bg-white shadow-lg border border-stone-200 rounded-full p-2 hover:bg-stone-50" data-dir="1" aria-label="Scroll right"><i data-lucide="chevron-right" class="w-5 h-5"></i></button>
      </div>
    </section>
  @endif
@endforeach

<script>
(function () {
  document.querySelectorAll('.marketplace-section').forEach(section => {
    const viewport = section.querySelector('.product-viewport');
    const track = section.querySelector('.product-track');
    const prev = section.querySelector('[data-dir="-1"]');
    const next = section.querySelector('[data-dir="1"]');
    if (!viewport || !track) return;

    let page = 0;
    const perView = () => window.innerWidth >= 1280 ? 6 : (window.innerWidth >= 1024 ? 4 : (window.innerWidth >= 640 ? 3 : 2));
    const total = () => track.children.length;
    const pages = () => Math.max(1, Math.ceil(total() / perView()));

    const apply = () => {
      page = Math.max(0, Math.min(page, pages() - 1));
      const width = viewport.clientWidth;
      track.style.transform = `translateX(${-page * width}px)`;
      prev.disabled = page === 0;
      next.disabled = page >= pages() - 1;
    };

    prev.addEventListener('click', () => { page--; apply(); });
    next.addEventListener('click', () => { page++; apply(); });
    apply();

    window.addEventListener('resize', apply);

    // touch swipe
    let startX = null;
    viewport.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
    viewport.addEventListener('touchend', e => {
      if (startX === null) return;
      const dx = e.changedTouches[0].clientX - startX;
      if (Math.abs(dx) > 40) { page += dx < 0 ? 1 : -1; apply(); }
      startX = null;
    }, { passive: true });
  });

  // countdown timers
  document.querySelectorAll('[data-countdown]').forEach(el => {
    const target = new Date(el.dataset.countdown).getTime();
    const clock = el.querySelector('.countdown-clock');
    const tick = () => {
      const remaining = target - Date.now();
      if (remaining <= 0) { clock.textContent = 'ENDED'; return; }
      const hours = Math.floor(remaining / 3600000);
      const minutes = Math.floor((remaining % 3600000) / 60000);
      const seconds = Math.floor((remaining % 60000) / 1000);
      clock.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    };
    tick();
    setInterval(tick, 1000);
  });
})();
</script>
@endsection
