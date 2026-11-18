<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', $storeName . ' — Fresh groceries delivered')</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>
tailwind.config = {
  theme: { extend: {
    colors: {
      brand: {50:'#FDECEA',100:'#FAD4CF',500:'#E4572E',600:'#D64533',700:'#B93A2B'},
      sun: {100:'#FEF3C7',300:'#FCD34D',400:'#FBBF24',500:'#F59E0B'},
      leaf: {50:'#EDFBF1',100:'#D3F5DF',500:'#22A45D',600:'#1B8A4C',700:'#15703D'},
      cream: '#FFFDF7'
    },
    fontFamily: { display: ['Georgia','serif'] }
  } }
}
</script>
@php $faviconSetting = \App\Support\Settings::get('favicon'); @endphp
<link rel="icon" href="{{ $faviconSetting ? asset('storage/' . $faviconSetting) : 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2245%22 fill=%22%23E4572E%22/></svg>' }}">
</head>
<body class="{{ request()->is('/') ? 'bg-sun-400 text-stone-950' : 'bg-cream text-stone-800' }} font-sans antialiased">
@php
  $topSettings = \App\Support\Settings::all();
  $navCategories = \App\Models\Category::whereNull('parent_id')->orderBy('sort_order')->orderBy('name')->take(12)->get();
  $navIcons = ['fruits' => 'apple', 'vegetables' => 'carrot', 'dairy-and-eggs' => 'milk', 'bakery' => 'wheat', 'pantry' => 'archive', 'beverages' => 'cup-soda'];
@endphp
@if(!empty($topSettings['promo_image']))
<a href="{{ $topSettings['promo_link'] ?: route('home') }}" class="block w-full">
  <img src="{{ asset('storage/' . $topSettings['promo_image']) }}" alt="Promotion" class="w-full h-9 sm:h-11 md:h-14 object-cover object-center">
</a>
@endif

<header class="bg-white border-b border-stone-100 sticky top-0 z-40 shadow-sm">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex items-center justify-between h-16 gap-3">
      <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
        @if($storeLogo)<img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="w-9 h-9 rounded-xl object-cover">@else<span class="w-9 h-9 rounded-xl bg-brand-500 text-white flex items-center justify-center"><i data-lucide="shopping-basket" class="w-5 h-5"></i></span>@endif
        <span class="font-display text-2xl font-bold text-stone-900">{{ $storeName }}</span>
      </a>
      <form action="{{ route('catalog') }}" method="GET" class="hidden md:flex flex-1 max-w-lg">
        <input name="q" value="{{ request('q') }}" placeholder="Search products..." class="w-full rounded-l-full border border-stone-200 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sun-400">
        <button class="rounded-r-full bg-brand-500 text-white px-5 text-sm font-semibold hover:bg-brand-600 flex items-center gap-1"><i data-lucide="search" class="w-4 h-4"></i>Search</button>
      </form>
      <nav class="flex items-center gap-3 text-sm">
        <a href="{{ route('catalog') }}" class="hidden sm:inline hover:text-brand-600 font-medium">Shop</a>
        @auth
          <div class="relative group">
            <button class="font-medium hover:text-brand-600 flex items-center gap-1"><i data-lucide="circle-user-round" class="w-5 h-5"></i><span class="hidden sm:inline">Hi, {{ Str::before(auth()->user()->name, ' ') }}</span></button>
            <div class="hidden group-hover:block absolute right-0 mt-1 w-52 bg-white rounded-xl shadow-lg border border-stone-100 py-2 z-50">
              <a href="{{ route('account.orders') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-leaf-50"><i data-lucide="package" class="w-4 h-4"></i>My Orders</a>
              <a href="{{ route('account.preferences') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-leaf-50"><i data-lucide="bell" class="w-4 h-4"></i>Notifications</a>
              <form action="{{ route('logout') }}" method="POST">@csrf
                <button class="flex items-center gap-2 w-full text-left px-4 py-2 hover:bg-brand-50 text-brand-600"><i data-lucide="log-out" class="w-4 h-4"></i>Logout</button>
              </form>
            </div>
          </div>
        @else
          <a href="{{ route('login') }}" class="font-medium hover:text-brand-600 flex items-center gap-1"><i data-lucide="log-in" class="w-5 h-5"></i>Login</a>
        @endauth
        <a href="{{ route('cart') }}" class="relative bg-leaf-500 text-white rounded-full px-4 py-2 font-semibold hover:bg-leaf-600 flex items-center gap-2">
          <i data-lucide="shopping-cart" class="w-5 h-5"></i><span class="hidden sm:inline">Cart</span>
          @php $cartCount = array_sum(session('cart', [])); @endphp
          @if($cartCount)<span class="absolute -top-2 -right-2 bg-sun-400 text-stone-900 text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center">{{ $cartCount }}</span>@endif
        </a>
      </nav>
    </div>
    <form action="{{ route('catalog') }}" method="GET" class="flex md:hidden pb-3">
      <input name="q" value="{{ request('q') }}" placeholder="Search products..." class="w-full rounded-l-full border border-stone-200 px-4 py-2 text-sm">
      <button class="rounded-r-full bg-brand-500 text-white px-4 text-sm flex items-center"><i data-lucide="search" class="w-4 h-4"></i></button>
    </form>
  </div>
  @if($navCategories->isNotEmpty())
  <div class="bg-[#FF0100]">
    <div class="max-w-7xl mx-auto px-2 sm:px-4 flex items-center gap-1 sm:gap-2 overflow-x-auto scrollbar-none justify-between">
      @foreach($navCategories as $cat)
        <a href="{{ route('catalog', ['category' => $cat->slug]) }}" class="flex items-center gap-1.5 px-3 sm:px-4 py-2 min-w-max text-white hover:text-sun-300 border-b-2 border-transparent hover:border-sun-400 transition font-medium">
          @if($cat->imageUrl())
            <img src="{{ $cat->imageUrl() }}" alt="{{ $cat->name }}" class="w-5 h-5 sm:w-6 sm:h-6 rounded object-cover">
          @else
            <i data-lucide="{{ $cat->icon ?: ($navIcons[$cat->slug] ?? 'package') }}" class="w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
          @endif
          <span class="text-[10px] sm:text-[11px] font-medium whitespace-nowrap">{{ $cat->name }}</span>
        </a>
      @endforeach
    </div>
  </div>
  @endif
</header>

<div id="flash-success" class="fixed top-4 right-4 z-50 max-w-sm w-full {{ !session('success') ? 'hidden' : '' }}">
  <div class="flex items-center gap-3 bg-leaf-600 text-white rounded-2xl shadow-2xl px-4 py-3 border border-leaf-500">
    <span class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center shrink-0"><i data-lucide="check-circle-2" class="w-6 h-6"></i></span>
    <div class="flex-1 min-w-0">
      <div class="text-xs uppercase tracking-widest font-bold text-leaf-100">{{ str_ends_with(session('success') ?? '', 'added to cart.') ? 'Added to cart' : 'Success' }}</div>
      <div class="text-sm font-semibold leading-snug">{{ session('success') }}</div>
    </div>
    <a href="{{ route('cart') }}" class="text-xs font-bold underline hover:text-leaf-100 shrink-0 hidden sm:inline">View cart</a>
    <button id="flash-close" class="p-1 text-leaf-100 hover:text-white" aria-label="Dismiss"><i data-lucide="x" class="w-5 h-5"></i></button>
  </div>
</div>
@if(session('error'))<div class="max-w-7xl mx-auto px-4 mt-4"><div class="flex items-center gap-2 bg-brand-50 border border-brand-500 text-brand-700 rounded-xl px-4 py-2 text-sm font-medium"><i data-lucide="alert-circle" class="w-5 h-5"></i>{{ session('error') }}</div></div>@endif
@if($errors->any())<div class="max-w-7xl mx-auto px-4 mt-4"><div class="flex items-center gap-2 bg-brand-50 border border-brand-500 text-brand-700 rounded-xl px-4 py-2 text-sm"><i data-lucide="alert-circle" class="w-5 h-5"></i>{{ $errors->first() }}</div></div>@endif

<main>@yield('content')</main>

<div id="cookie-banner" class="hidden fixed inset-x-0 bottom-0 z-50 p-3 sm:p-4">
  <div class="max-w-4xl mx-auto bg-stone-900 text-stone-200 rounded-2xl shadow-2xl border border-stone-800 overflow-hidden">
    <div id="cookie-main" class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4">
      <div class="flex items-start gap-3 flex-1">
        <i data-lucide="cookie" class="w-7 h-7 text-sun-400 shrink-0 mt-0.5"></i>
        <p class="text-xs leading-relaxed">We use cookies to improve your shopping experience, remember your preferences, analyze website traffic and provide relevant offers. You can accept all cookies, reject optional cookies or customize your preferences.</p>
      </div>
      <div class="flex gap-2 shrink-0">
        <button id="cookie-customize" class="px-4 py-2 text-xs font-semibold border border-stone-700 rounded-full hover:bg-stone-800">Customize settings</button>
        <button id="cookie-accept" class="px-4 py-2 text-xs font-bold bg-sun-400 text-stone-950 rounded-full hover:bg-sun-300">Accept all cookies</button>
      </div>
    </div>
    <div id="cookie-panel" class="hidden border-t border-stone-800 p-4 sm:p-5 bg-stone-950/60">
      <div class="grid sm:grid-cols-2 gap-3 text-xs">
        <label class="flex items-start gap-2 bg-stone-900/60 border border-stone-800 rounded-xl p-3"><input type="checkbox" checked disabled class="mt-0.5"> <span><b class="text-white">Essential cookies</b><br><span class="text-stone-400">Required for login, shopping cart, checkout and website security. Always active.</span></span></label>
        <label class="flex items-start gap-2 bg-stone-900/60 border border-stone-800 rounded-xl p-3 cursor-pointer"><input type="checkbox" id="consent-functional" class="mt-0.5"> <span><b class="text-white">Functional cookies</b><br><span class="text-stone-400">Remember your preferences (language, currency, recently viewed).</span></span></label>
        <label class="flex items-start gap-2 bg-stone-900/60 border border-stone-800 rounded-xl p-3 cursor-pointer"><input type="checkbox" id="consent-analytics" class="mt-0.5"> <span><b class="text-white">Analytics cookies</b><br><span class="text-stone-400">Help us understand website usage and improve it.</span></span></label>
        <label class="flex items-start gap-2 bg-stone-900/60 border border-stone-800 rounded-xl p-3 cursor-pointer"><input type="checkbox" id="consent-marketing" class="mt-0.5"> <span><b class="text-white">Marketing cookies</b><br><span class="text-stone-400">Used for relevant promotional offers and advertising.</span></span></label>
      </div>
      <div class="flex flex-wrap gap-2 justify-end mt-3">
        <button id="cookie-reject" class="px-4 py-2 text-xs font-semibold border border-stone-700 rounded-full hover:bg-stone-800">Reject optional cookies</button>
        <button id="cookie-save" class="px-5 py-2 text-xs font-bold bg-leaf-500 text-white rounded-full hover:bg-leaf-600">Save preferences</button>
      </div>
    </div>
  </div>
</div>

<footer class="bg-stone-950 text-stone-400 mt-16 text-xs">
  <div class="border-b border-stone-800">
    <div class="max-w-7xl mx-auto px-4 py-5 flex flex-col md:flex-row md:items-center gap-4 justify-between">
      <div>
        <div class="text-white font-bold text-base">New to {{ $storeName }}?</div>
        <p class="text-stone-500 mt-0.5 max-w-md">Subscribe to our newsletter for the latest offers. Unsubscribe anytime as described in the <a href="{{ route('legal') }}#privacy" class="underline hover:text-white">Privacy Policy</a>.</p>
      </div>
      <form action="{{ route('newsletter.subscribe') }}" method="POST" class="w-full md:w-auto">@csrf
        <label class="flex items-start gap-2 text-[11px] text-stone-500 mb-2 max-w-sm"><input type="checkbox" name="accept_terms" value="1" required class="mt-0.5"> <span>I accept the <a href="{{ route('legal') }}#terms" class="underline hover:text-white">Legal Terms</a> and <a href="{{ route('legal') }}#privacy" class="underline hover:text-white">Privacy Policy</a>.</span></label>
        <div class="flex">
          <input type="email" name="email" required placeholder="Enter email address" class="flex-1 w-64 sm:w-72 bg-stone-900 border border-stone-700 rounded-l-xl px-4 py-2.5 text-sm text-white placeholder-stone-600 focus:outline-none focus:ring-2 focus:ring-sun-400">
          <button class="bg-sun-400 hover:bg-sun-300 text-stone-950 font-bold rounded-r-xl px-5 text-sm transition">Subscribe</button>
        </div>
      </form>
    </div>
  </div>

  <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-6">
    <div>
      <div class="text-white font-bold mb-2">NEED HELP?</div>
      @php $wa = preg_replace('/\D+/', '', \App\Support\Settings::get('store_phone', '')); @endphp
      @if($wa)<a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="flex items-center gap-1.5 hover:text-white py-0.5"><i data-lucide="message-circle" class="w-4 h-4 text-leaf-500"></i>Chat with us</a>@endif
      <a href="mailto:{{ \App\Support\Settings::get('store_email', 'support@shopfresh.test') }}" class="flex items-center gap-1.5 hover:text-white py-0.5"><i data-lucide="help-circle" class="w-4 h-4 text-leaf-500"></i>Help Center</a>
      @if($topSettings = \App\Support\Settings::get('store_phone'))<a href="tel:{{ $topSettings }}" class="flex items-center gap-1.5 hover:text-white py-0.5"><i data-lucide="phone" class="w-4 h-4 text-leaf-500"></i>{{ $topSettings }}</a>@endif
      @if(\App\Support\Settings::get('store_address'))<span class="flex items-start gap-1.5 py-0.5"><i data-lucide="map-pin" class="w-4 h-4 text-leaf-500 shrink-0"></i>{{ \App\Support\Settings::get('store_address') }}</span>@endif
    </div>
    <div>
      <div class="text-white font-bold mb-2">USEFUL LINKS</div>
      <a href="{{ route('account.orders') }}" class="block hover:text-white py-0.5">Track Your Order</a>
      <a href="{{ route('legal') }}#delivery" class="block hover:text-white py-0.5">Shipping &amp; Delivery</a>
      <a href="{{ route('legal') }}#returns" class="block hover:text-white py-0.5">Return Policy</a>
      <a href="{{ route('legal') }}#how-to-order" class="block hover:text-white py-0.5">How to Order</a>
    </div>
    <div>
      <div class="text-white font-bold mb-2">ABOUT {{ strtoupper($storeName) }}</div>
      <a href="{{ route('legal') }}#about" class="block hover:text-white py-0.5">About us</a>
      <a href="{{ route('legal') }}#returns" class="block hover:text-white py-0.5">Returns &amp; Refunds</a>
      <a href="{{ route('legal') }}#privacy" class="block hover:text-white py-0.5">Privacy Notice</a>
      <a href="{{ route('legal') }}#terms" class="block hover:text-white py-0.5">Terms and Conditions</a>
    </div>
    <div>
      <div class="text-white font-bold mb-2">MAKE MONEY WITH {{ strtoupper($storeName) }}</div>
      <a href="mailto:{{ \App\Support\Settings::get('store_email', 'support@shopfresh.test') }}?subject=Sell%20on%20{{ $storeName }}" class="block hover:text-white py-0.5">Sell on {{ $storeName }}</a>
      <a href="mailto:{{ \App\Support\Settings::get('store_email', 'support@shopfresh.test') }}?subject=Sales%20Consultant" class="block hover:text-white py-0.5">Become a Sales Consultant</a>
      <a href="mailto:{{ \App\Support\Settings::get('store_email', 'support@shopfresh.test') }}?subject=Corporate%20%26%20Bulk" class="block hover:text-white py-0.5">Corporate &amp; Bulk Purchase</a>
      <a href="mailto:{{ \App\Support\Settings::get('store_email', 'support@shopfresh.test') }}?subject=Report%20Product" class="block hover:text-white py-0.5">Report a Product</a>
    </div>
    <div>
      <div class="text-white font-bold mb-2">DOWNLOAD {{ strtoupper($storeName) }} FREE APP</div>
      <a href="https://play.google.com/store" target="_blank" rel="noopener" class="flex items-center gap-1.5 bg-stone-800 hover:bg-stone-700 rounded-lg px-3 py-2 mt-1"><i data-lucide="smartphone" class="w-5 h-5 text-sun-400"></i><span><span class="block text-[9px] text-stone-500">GET IT ON</span>Google Play</span></a>
      <a href="https://apps.apple.com/app" target="_blank" rel="noopener" class="flex items-center gap-1.5 bg-stone-800 hover:bg-stone-700 rounded-lg px-3 py-2 mt-2"><i data-lucide="apple" class="w-5 h-5 text-white"></i><span><span class="block text-[9px] text-stone-500">DOWNLOAD ON THE</span>App Store</span></a>
    </div>
    <div>
      <div class="text-white font-bold mb-2">JOIN US ON</div>
      @php $footerSocials = array_filter(['facebook' => \App\Support\Settings::get('social_facebook'), 'instagram' => \App\Support\Settings::get('social_instagram'), 'twitter' => \App\Support\Settings::get('social_twitter'), 'tiktok' => \App\Support\Settings::get('social_tiktok')]); @endphp
      @if($footerSocials)
        <div class="flex flex-col gap-1.5">
          @foreach($footerSocials as $name => $url)<a href="{{ $url }}" target="_blank" rel="noopener" class="flex items-center gap-1.5 hover:text-white"><i data-lucide="{{ $name }}" class="w-4 h-4"></i>{{ ucfirst($name) }}</a>@endforeach
        </div>
      @else
        <p class="text-stone-500">Follow us soon — add social links in Admin → Settings.</p>
      @endif
      <div class="text-white font-bold mt-5 mb-2">PAYMENT METHODS</div>
      <div class="flex flex-wrap gap-1.5">
        <span class="bg-stone-800 rounded-md px-2 py-1 flex items-center gap-1"><i data-lucide="smartphone" class="w-3.5 h-3.5 text-leaf-500"></i>M-Pesa</span>
        <span class="bg-stone-800 rounded-md px-2 py-1 flex items-center gap-1"><i data-lucide="banknote" class="w-3.5 h-3.5 text-sun-400"></i>COD</span>
        <span class="bg-stone-800 rounded-md px-2 py-1 flex items-center gap-1"><i data-lucide="credit-card" class="w-3.5 h-3.5 text-white"></i>Card</span>
      </div>
    </div>
  </div>

  <div class="border-t border-stone-800">
    <div class="max-w-7xl mx-auto px-4 py-4 text-[10px] text-stone-500 grid lg:grid-cols-3 gap-3 items-center">
      <div>
        <span class="text-stone-400 font-semibold">Business Name:</span> {{ $storeName }}<br>
        <span class="text-stone-400 font-semibold">Phone:</span> {{ \App\Support\Settings::get('store_phone') ?: '+254 700 000 000' }}
      </div>
      <div class="text-center">
        <span class="text-stone-400 font-semibold">Address:</span> {{ \App\Support\Settings::get('store_address') ?: 'Nairobi, Kenya' }}
      </div>
      <div class="lg:text-right">
        © {{ date('Y') }} {{ $storeName }}. All rights reserved.<br>
        <button id="cookie-settings-link" type="button" class="underline hover:text-white mt-1">Cookie Settings</button>
      </div>
    </div>
  </div>
</footer>
<script>lucide.createIcons();
(function () {
  const flash = document.getElementById('flash-success');
  if (flash && !flash.classList.contains('hidden')) {
    const hide = () => flash.classList.add('hidden');
    document.getElementById('flash-close')?.addEventListener('click', hide);
    setTimeout(hide, 4000);
  }
})();
(function () {
  const banner = document.getElementById('cookie-banner');
  if (!banner) return;
  let saved = null;
  try { saved = JSON.parse(localStorage.getItem('cookie_consent') || 'null'); } catch {}
  if (!saved) banner.classList.remove('hidden');

  const panel = document.getElementById('cookie-panel');
  const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const saveConsent = async (functional, analytics, marketing) => {
    try {
      await fetch('{{ route('cookie.consent') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
        body: JSON.stringify({ functional, analytics, marketing }),
        credentials: 'same-origin',
      });
    } catch (e) {}
    localStorage.setItem('cookie_consent', JSON.stringify({ functional, analytics, marketing, at: Date.now() }));
    banner.classList.add('hidden');
  };

  document.getElementById('cookie-accept')?.addEventListener('click', () => saveConsent(true, true, true));
  document.getElementById('cookie-reject')?.addEventListener('click', () => saveConsent(false, false, false));
  document.getElementById('cookie-save')?.addEventListener('click', () => saveConsent(
    document.getElementById('consent-functional').checked,
    document.getElementById('consent-analytics').checked,
    document.getElementById('consent-marketing').checked
  ));
  document.getElementById('cookie-customize')?.addEventListener('click', () => panel?.classList.toggle('hidden'));
  document.getElementById('cookie-settings-link')?.addEventListener('click', () => {
    localStorage.removeItem('cookie_consent');
    banner.classList.remove('hidden');
  });
})();
</script>
</script>
</body>
</html>
