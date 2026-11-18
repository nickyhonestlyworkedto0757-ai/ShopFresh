@extends('admin.layout')
@section('title', 'Sales report — Admin')
@section('content')
<div class="flex items-center justify-between flex-wrap gap-3">
  <h1 class="text-2xl font-bold">Sales report</h1>
  <div class="flex gap-2">
    <a href="{{ route('admin.reports.sales.csv', request()->query()) }}" class="inline-flex items-center gap-1 border border-stone-300 dark:border-stone-700 rounded-full px-4 py-2 text-sm font-semibold"><i data-lucide="file-spreadsheet" class="w-4 h-4"></i>Excel (CSV)</a>
    <a href="{{ route('admin.reports.sales.pdf', request()->query()) }}" class="inline-flex items-center gap-1 border border-stone-300 dark:border-stone-700 rounded-full px-4 py-2 text-sm font-semibold"><i data-lucide="file-text" class="w-4 h-4"></i>PDF</a>
  </div>
</div>
<form method="GET" class="flex flex-wrap gap-2 mt-4">
  <div><label class="text-xs font-semibold text-stone-500">From</label><input type="date" name="from" value="{{ $from }}" class="mt-1 block border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 rounded-xl px-3 py-2 text-sm"></div>
  <div><label class="text-xs font-semibold text-stone-500">To</label><input type="date" name="to" value="{{ $to }}" class="mt-1 block border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 rounded-xl px-3 py-2 text-sm"></div>
  <div class="self-end"><button class="flex items-center gap-1 bg-stone-900 dark:bg-white text-white dark:text-stone-900 rounded-xl px-4 py-2 text-sm font-semibold"><i data-lucide="search" class="w-4 h-4"></i>Run</button></div>
</form>
<div class="grid grid-cols-2 gap-3 mt-4 max-w-md">
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-4"><div class="text-xs text-stone-500">Orders</div><div class="text-2xl font-black">{{ number_format($totals['orders']) }}</div></div>
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-4"><div class="text-xs text-stone-500">Revenue</div><div class="text-2xl font-black text-leaf-600 dark:text-leaf-100">{{ $currency }} {{ number_format($totals['revenue'], 2) }}</div></div>
</div>
<div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden mt-4">
  <table class="w-full text-sm">
    <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3">Date</th><th class="p-3">Orders</th><th class="p-3 text-right">Revenue</th></tr></thead>
    <tbody>
      @forelse($rows as $row)
        <tr class="border-t border-stone-100 dark:border-stone-800"><td class="p-3">{{ \Illuminate\Support\Carbon::parse($row->day)->format('d M Y') }}</td><td class="p-3">{{ $row->orders }}</td><td class="p-3 text-right">{{ $currency }} {{ number_format($row->total, 2) }}</td></tr>
      @empty
        <tr><td colspan="3" class="p-6 text-center text-stone-400">No sales in this period.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
