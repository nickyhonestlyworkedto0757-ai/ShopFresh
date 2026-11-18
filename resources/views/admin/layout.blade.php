<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Admin — ' . $storeName)</title>
@php $faviconSetting = \App\Support\Settings::get('favicon'); @endphp
<link rel="icon" href="{{ $faviconSetting ? asset('storage/' . $faviconSetting) : 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2245%22 fill=%22%23E4572E%22/></svg>' }}">
<script>
  if (localStorage.getItem('admin-theme') === 'dark' || (!('admin-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
  }
</script>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
tailwind.config = {
  darkMode: 'class',
  theme: { extend: { colors: {
    brand: {50:'#FDECEA',500:'#E4572E',600:'#D64533'},
    sun: {100:'#FEF3C7',400:'#FBBF24'},
    leaf: {50:'#EDFBF1',100:'#D3F5DF',500:'#22A45D',600:'#1B8A4C',700:'#15703D'}
  } } }
}
</script>
</head>
@php $staff = Auth::guard('admin')->user(); @endphp
<body class="h-full bg-stone-100 dark:bg-stone-950 text-stone-800 dark:text-stone-200">
<div class="flex min-h-screen">
  <aside class="w-64 bg-stone-900 text-stone-300 flex-shrink-0 hidden md:flex flex-col fixed inset-y-0 left-0 z-30 overflow-y-auto">
    <a href="{{ route('home') }}" class="flex items-center gap-2 px-6 py-5 border-b border-stone-800 text-white font-bold text-xl shrink-0">
      @if($storeLogo)<img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="w-8 h-8 rounded-lg object-cover">@else<i data-lucide="shopping-basket" class="w-6 h-6 text-sun-400"></i>@endif
      <span class="truncate">{{ $storeName }}<span class="text-sun-400 text-sm align-top"> Admin</span></span>
    </a>
    <nav class="p-3 space-y-0.5 text-sm flex-1">
      <p class="px-3 pt-2 pb-1 text-[10px] uppercase tracking-widest text-stone-500">Overview</p>
      <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.dashboard') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="layout-dashboard" class="w-4 h-4"></i>Dashboard</a>

      @if($staff->hasPermission('products') || $staff->hasPermission('categories') || $staff->hasPermission('banners') || $staff->hasPermission('coupons'))
        <p class="px-3 pt-4 pb-1 text-[10px] uppercase tracking-widest text-stone-500">Catalog</p>
        @if($staff->hasPermission('products'))<a href="{{ route('admin.products.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.products.*') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="package" class="w-4 h-4"></i>Products</a>
        <a href="{{ route('admin.homepage-sections.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.homepage-sections.*') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="layout-grid" class="w-4 h-4"></i>Homepage Sections</a>
        @endif
        @if($staff->hasPermission('categories'))<a href="{{ route('admin.categories.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.categories.*') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="tags" class="w-4 h-4"></i>Categories</a>@endif
        @if($staff->hasPermission('banners'))<a href="{{ route('admin.banners.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.banners.*') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="images" class="w-4 h-4"></i>Banners</a>@endif
        @if($staff->hasPermission('coupons'))<a href="{{ route('admin.coupons.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.coupons.*') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="ticket" class="w-4 h-4"></i>Coupons</a>@endif
      @endif

      @if($staff->hasPermission('orders.view') || $staff->hasPermission('payments') || $staff->hasPermission('delivery') || $staff->hasPermission('customers'))
        <p class="px-3 pt-4 pb-1 text-[10px] uppercase tracking-widest text-stone-500">Sales</p>
        @if($staff->hasPermission('orders.view'))<a href="{{ route('admin.orders.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.orders.*') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="receipt" class="w-4 h-4"></i>Orders</a>@endif
        @if($staff->hasPermission('payments'))<a href="{{ route('admin.payments.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.payments.*') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="credit-card" class="w-4 h-4"></i>Payments</a>@endif
        @if($staff->hasPermission('delivery'))<a href="{{ route('admin.delivery.zones') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.delivery.*') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="truck" class="w-4 h-4"></i>Delivery Zones</a>@endif
        @if($staff->hasPermission('customers'))<a href="{{ route('admin.customers.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.customers.*') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="users" class="w-4 h-4"></i>Customers</a>@endif
        <a href="{{ route('admin.newsletter.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.newsletter.*') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="mail-check" class="w-4 h-4"></i>Newsletter</a>
      @endif

      @if($staff->hasPermission('reports'))
        <p class="px-3 pt-4 pb-1 text-[10px] uppercase tracking-widest text-stone-500">Insights</p>
        <a href="{{ route('admin.reports.sales') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.reports.sales*') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="chart-line" class="w-4 h-4"></i>Sales Report</a>
        <a href="{{ route('admin.reports.profit') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.reports.profit') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="trending-up" class="w-4 h-4"></i>Profit Report</a>
        <a href="{{ route('admin.reports.products') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.reports.products') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="package-search" class="w-4 h-4"></i>Product Report</a>
        <a href="{{ route('admin.reports.customers') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.reports.customers') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="user-chart" class="w-4 h-4"></i>Customer Report</a>
      @endif

      <p class="px-3 pt-4 pb-1 text-[10px] uppercase tracking-widest text-stone-500">System</p>
      <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.profile.*') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="user-cog" class="w-4 h-4"></i>My Profile</a>
      @if($staff->hasPermission('settings'))
        <a href="{{ route('admin.settings.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.settings.*') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="settings" class="w-4 h-4"></i>Settings</a>
        <a href="{{ route('admin.cookies.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.cookies.*') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="cookie" class="w-4 h-4"></i>Cookie Consent</a>
        <a href="{{ route('admin.admin-users.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.admin-users.*') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="shield-check" class="w-4 h-4"></i>Admin Users</a>
        <a href="{{ route('admin.activity.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-stone-800 {{ request()->routeIs('admin.activity.*') ? 'bg-stone-800 text-white' : '' }}"><i data-lucide="history" class="w-4 h-4"></i>Activity Log</a>
      @endif
    </nav>
    <div class="p-4 border-t border-stone-800 text-xs shrink-0">
      <div class="font-semibold text-white truncate">{{ $staff->name }}</div>
      <div class="text-stone-500 capitalize">{{ str_replace('_', ' ', $staff->role) }}</div>
      <form action="{{ route('admin.logout') }}" method="POST" class="mt-2">@csrf<button class="flex items-center gap-1 text-brand-500 font-bold hover:text-brand-600"><i data-lucide="log-out" class="w-4 h-4"></i>Logout</button></form>
    </div>
  </aside>

  <div class="flex-1 md:pl-64">
    <header class="sticky top-0 z-20 bg-white/90 dark:bg-stone-900/90 backdrop-blur border-b border-stone-200 dark:border-stone-800 px-4 md:px-6 h-14 flex items-center justify-between gap-2">
      <div class="flex items-center gap-2 md:hidden">
        <a href="{{ route('admin.dashboard') }}" class="font-bold">{{ $storeName }} Admin</a>
      </div>
      <div class="hidden md:block text-sm text-stone-500 dark:text-stone-400 truncate">@yield('title', 'Dashboard')</div>
      <div class="flex items-center gap-2">
        <button id="theme-toggle" class="p-2 rounded-lg hover:bg-stone-100 dark:hover:bg-stone-800" aria-label="Toggle dark mode">
          <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
          <i data-lucide="moon" class="w-5 h-5 block dark:hidden"></i>
        </button>
        <a href="{{ route('admin.notifications.index') }}" class="relative p-2 rounded-lg hover:bg-stone-100 dark:hover:bg-stone-800" aria-label="Notifications">
          <i data-lucide="bell" class="w-5 h-5"></i>
          @if($adminUnreadCount > 0)
            <span class="absolute top-1 right-1 bg-brand-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center">{{ $adminUnreadCount > 99 ? '99+' : $adminUnreadCount }}</span>
          @endif
        </a>
        <a href="{{ route('home') }}" class="p-2 rounded-lg hover:bg-stone-100 dark:hover:bg-stone-800" title="View storefront"><i data-lucide="external-link" class="w-5 h-5"></i></a>
      </div>
    </header>
    <main class="p-4 md:p-6">
      @if(session('success'))<div class="flex items-center gap-2 bg-leaf-50 dark:bg-leaf-700/20 border border-leaf-500 text-leaf-700 dark:text-leaf-100 rounded-xl px-4 py-2 text-sm font-medium mb-4"><i data-lucide="check-circle-2" class="w-5 h-5"></i>{{ session('success') }}</div>@endif
      @if(session('error'))<div class="flex items-center gap-2 bg-brand-50 border border-brand-500 text-brand-600 rounded-xl px-4 py-2 text-sm mb-4"><i data-lucide="alert-circle" class="w-5 h-5"></i>{{ session('error') }}</div>@endif
      @if($errors->any())<div class="flex items-center gap-2 bg-brand-50 border border-brand-500 text-brand-600 rounded-xl px-4 py-2 text-sm mb-4"><i data-lucide="alert-circle" class="w-5 h-5"></i>{{ $errors->first() }}</div>@endif
      @yield('content')
    </main>
  </div>
</div>
<script>
  document.getElementById('theme-toggle')?.addEventListener('click', () => {
    const dark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('admin-theme', dark ? 'dark' : 'light');
  });
  lucide.createIcons();
</script>
</body>
</html>
