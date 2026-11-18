@extends('admin.layout')
@section('title', 'Profit report — Admin')
@section('content')
<h1 class="text-2xl font-bold">Profit report</h1>
<p class="text-sm text-stone-500 dark:text-stone-400 mt-1">Based on recorded cost prices. Set cost prices on your products to see real margins.</p>
<form method="GET" class="flex flex-wrap gap-2 mt-4">
  <div><label class="text-xs font-semibold text-stone-500">From</label><input type="date" name="from" value="{{ $from }}" class="mt-1 block border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 rounded-xl px-3 py-2 text-sm"></div>
  <div><label class="text-xs font-semibold text-stone-500">To</label><input type="date" name="to" value="{{ $to }}" class="mt-1 block border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 rounded-xl px-3 py-2 text-sm"></div>
  <div class="self-end"><button class="flex items-center gap-1 bg-stone-900 dark:bg-white text-white dark:text-stone-900 rounded-xl px-4 py-2 text-sm font-semibold"><i data-lucide="search" class="w-4 h-4"></i>Run</button></div>
</form>
@php $revenue = $rows->sum('revenue'); $cost = $rows->sum('cost'); $profit = $rows->sum('profit'); @endphp
<div class="grid grid-cols-3 gap-3 mt-4 max-w-2xl">
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-4"><div class="text-xs text-stone-500">Revenue</div><div class="text-xl font-black">{{ $currency }} {{ number_format($revenue, 0) }}</div></div>
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-4"><div class="text-xs text-stone-500">Cost</div><div class="text-xl font-black">{{ $currency }} {{ number_format($cost, 0) }}</div></div>
  <div class="bg-leaf-600 rounded-2xl p-4 text-white"><div class="text-xs text-leaf-100">Profit</div><div class="text-xl font-black">{{ $currency }} {{ number_format($profit, 0) }}</div></div>
</div>
<div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden mt-4 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3">Product</th><th class="p-3">Qty</th><th class="p-3 text-right">Revenue</th><th class="p-3 text-right">Cost</th><th class="p-3 text-right">Profit</th></tr></thead>
    <tbody>
      @forelse($rows as $row)
        <tr class="border-t border-stone-100 dark:border-stone-800"><td class="p-3 font-medium">{{ $row->product_name }}</td><td class="p-3">{{ $row->qty }}</td><td class="p-3 text-right">{{ number_format($row->revenue, 2) }}</td><td class="p-3 text-right">{{ number_format($row->cost, 2) }}</td><td class="p-3 text-right font-bold {{ $row->profit >= 0 ? 'text-leaf-700 dark:text-leaf-100' : 'text-brand-600' }}">{{ number_format($row->profit, 2) }}</td></tr>
      @empty
        <tr><td colspan="5" class="p-6 text-center text-stone-400">No sales in this period.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
