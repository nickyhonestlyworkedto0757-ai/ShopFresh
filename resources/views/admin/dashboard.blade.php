@extends('admin.layout')
@section('title', 'Dashboard — ' . $storeName)
@section('content')
<div class="flex items-center justify-between flex-wrap gap-3">
  <div>
    <h1 class="text-2xl font-bold">Dashboard</h1>
    <p class="text-sm text-stone-500 dark:text-stone-400">Live overview of your store, updated from real data.</p>
  </div>
  <div class="text-right">
    <div class="text-xs text-stone-500 dark:text-stone-400">Today's revenue</div>
    <div class="text-2xl font-black text-leaf-600 dark:text-leaf-100">{{ $currency }} {{ number_format($stats['revenue_today'], 2) }}</div>
    <div class="text-[11px] text-stone-400">{{ $stats['orders_today'] }} orders today</div>
  </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-3 mt-5">
  @foreach([
    ['Total revenue', $currency . ' ' . number_format($stats['revenue'], 0), 'banknote', 'text-leaf-600 dark:text-leaf-100'],
    ['Total orders', $stats['orders'], 'receipt', 'text-stone-600 dark:text-stone-300'],
    ['Customers', $stats['customers'], 'users', 'text-stone-600 dark:text-stone-300'],
    ['Products', $stats['products'], 'package', 'text-stone-600 dark:text-stone-300'],
    ['Pending', $stats['pending'], 'clock', 'text-sun-400'],
    ['Processing', $stats['processing'], 'loader', 'text-sun-400'],
    ['Shipped', $stats['shipped'], 'truck', 'text-stone-600 dark:text-stone-300'],
    ['Delivered', $stats['delivered'], 'check-circle-2', 'text-leaf-600 dark:text-leaf-100'],
    ['Low stock', $stats['low_stock'], 'alert-triangle', $stats['low_stock'] > 0 ? 'text-brand-500' : 'text-stone-400'],
  ] as [$label, $value, $icon, $color])
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-4">
      <i data-lucide="{{ $icon }}" class="w-6 h-6 {{ $color }}"></i>
      <div class="text-xl font-black mt-1 truncate">{{ $value }}</div>
      <div class="text-[11px] text-stone-500 dark:text-stone-400">{{ $label }}</div>
    </div>
  @endforeach
</div>

<div class="grid lg:grid-cols-3 gap-4 mt-5">
  <div class="lg:col-span-2 bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-5">
    <div class="flex items-center justify-between mb-3">
      <h2 class="font-bold flex items-center gap-2"><i data-lucide="chart-line" class="w-5 h-5"></i>Sales — last 30 days</h2>
      <a href="{{ route('admin.reports.sales') }}" class="text-xs text-brand-600 font-semibold hover:underline">Full report</a>
    </div>
    <canvas id="dailyChart" height="110"></canvas>
  </div>
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-5">
    <h2 class="font-bold flex items-center gap-2 mb-3"><i data-lucide="pie-chart" class="w-5 h-5"></i>Payment methods</h2>
    <canvas id="paymentChart" height="220"></canvas>
  </div>
</div>

<div class="grid lg:grid-cols-3 gap-4 mt-4">
  <div class="lg:col-span-2 bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-5">
    <h2 class="font-bold flex items-center gap-2 mb-3"><i data-lucide="bar-chart-3" class="w-5 h-5"></i>Monthly revenue — last 12 months</h2>
    <canvas id="monthlyChart" height="110"></canvas>
  </div>
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-5">
    <h2 class="font-bold flex items-center gap-2 mb-3"><i data-lucide="trophy" class="w-5 h-5 text-sun-400"></i>Best sellers</h2>
    <ol class="divide-y divide-stone-100 dark:divide-stone-800">
      @forelse($bestSellers as $item)
        <li class="py-2 flex items-center justify-between gap-2">
          <span class="text-sm font-medium truncate">{{ $item->product_name }}</span>
          <span class="text-xs text-stone-500 whitespace-nowrap">{{ $item->qty }} sold · {{ $currency }} {{ number_format($item->revenue, 0) }}</span>
        </li>
      @empty
        <li class="py-6 text-center text-sm text-stone-400">No sales yet.</li>
      @endforelse
    </ol>
  </div>
</div>

<div class="grid lg:grid-cols-2 gap-4 mt-4">
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden">
    <div class="flex items-center justify-between p-4 border-b border-stone-100 dark:border-stone-800">
      <h2 class="font-bold flex items-center gap-2"><i data-lucide="receipt" class="w-5 h-5"></i>Recent orders</h2>
      <a href="{{ route('admin.orders.index') }}" class="text-xs text-brand-600 font-semibold hover:underline">View all</a>
    </div>
    <table class="w-full text-sm">
      <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3">Order</th><th class="p-3">Total</th><th class="p-3">Status</th><th></th></tr></thead>
      <tbody>
        @forelse($recentOrders as $order)
          <tr class="border-t border-stone-100 dark:border-stone-800">
            <td class="p-3 font-semibold">{{ $order->order_number }}<div class="text-[10px] text-stone-400 font-normal">{{ $order->customer_name }}</div></td>
            <td class="p-3">{{ $currency }} {{ number_format($order->total, 2) }}</td>
            <td class="p-3"><span class="text-[10px] font-bold uppercase rounded-full px-2 py-0.5 {{ $order->status === 'delivered' ? 'bg-leaf-100 text-leaf-700' : ($order->status === 'cancelled' ? 'bg-brand-50 text-brand-600' : 'bg-sun-100 text-stone-700') }}">{{ $order->status }}</span></td>
            <td class="p-3 text-right"><a class="text-brand-600" href="{{ route('admin.orders.show', $order) }}"><i data-lucide="arrow-right" class="w-4 h-4"></i></a></td>
          </tr>
        @empty
          <tr><td colspan="4" class="p-6 text-center text-stone-400">No orders yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden">
    <div class="flex items-center justify-between p-4 border-b border-stone-100 dark:border-stone-800">
      <h2 class="font-bold flex items-center gap-2"><i data-lucide="alert-triangle" class="w-5 h-5 text-sun-400"></i>Low stock alerts</h2>
      <a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="text-xs text-brand-600 font-semibold hover:underline">View all</a>
    </div>
    <table class="w-full text-sm">
      <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3">Product</th><th class="p-3">Stock</th><th class="p-3">Threshold</th></tr></thead>
      <tbody>
        @forelse($lowStockProducts as $product)
          <tr class="border-t border-stone-100 dark:border-stone-800">
            <td class="p-3 font-medium">{{ $product->name }}</td>
            <td class="p-3 font-bold {{ $product->stock === 0 ? 'text-brand-600' : 'text-stone-700 dark:text-stone-200' }}">{{ $product->stock }}</td>
            <td class="p-3 text-stone-500">{{ $product->low_stock_threshold }}</td>
          </tr>
        @empty
          <tr><td colspan="3" class="p-6 text-center text-stone-400">All products sufficiently stocked.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<script>
const isDark = document.documentElement.classList.contains('dark');
const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)';
const textColor = isDark ? '#d6d3d1' : '#57534e';
new Chart(document.getElementById('dailyChart'), {
  type: 'line',
  data: { labels: @json($dailyLabels), datasets: [{ label: 'Revenue', data: @json($dailyTotals), borderColor: '#E4572E', backgroundColor: 'rgba(228,87,46,0.12)', fill: true, tension: 0.35, pointRadius: 2 }] },
  options: { plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { color: textColor, maxTicksLimit: 10 } }, y: { grid: { color: gridColor }, ticks: { color: textColor } } } }
});
new Chart(document.getElementById('monthlyChart'), {
  type: 'bar',
  data: { labels: @json($monthlyLabels), datasets: [{ label: 'Revenue', data: @json($monthlyTotals), backgroundColor: '#22A45D', borderRadius: 6 }] },
  options: { plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { color: textColor } }, y: { grid: { color: gridColor }, ticks: { color: textColor } } } }
});
new Chart(document.getElementById('paymentChart'), {
  type: 'doughnut',
  data: { labels: @json($paymentBreakdown->keys()), datasets: [{ data: @json($paymentBreakdown->values()), backgroundColor: ['#22A45D', '#FBBF24', '#E4572E', '#78716c'], borderWidth: 0 }] },
  options: { plugins: { legend: { position: 'bottom', labels: { color: textColor } } } }
});
</script>
@endsection
