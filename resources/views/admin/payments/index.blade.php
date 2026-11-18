@extends('admin.layout')
@section('title', 'M-Pesa & Payments — Admin')
@section('content')
<div class="flex items-center justify-between flex-wrap gap-3">
  <div>
    <h1 class="text-2xl font-bold">Payments</h1>
    <p class="text-sm text-stone-500 dark:text-stone-400">M-Pesa transactions, card and cash on delivery.</p>
  </div>
  <div class="flex gap-2">
    <a href="{{ route('admin.payments.export.csv', request()->query()) }}" class="inline-flex items-center gap-1 border border-stone-300 dark:border-stone-700 rounded-full px-4 py-2 text-sm font-semibold"><i data-lucide="file-spreadsheet" class="w-4 h-4"></i>Excel (CSV)</a>
    <a href="{{ route('admin.payments.export.pdf', request()->query()) }}" class="inline-flex items-center gap-1 border border-stone-300 dark:border-stone-700 rounded-full px-4 py-2 text-sm font-semibold"><i data-lucide="file-text" class="w-4 h-4"></i>PDF</a>
  </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-6 gap-3 mt-4">
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-4"><i data-lucide="check-circle-2" class="w-6 h-6 text-leaf-600"></i><div class="text-lg font-black mt-1">{{ $currency }} {{ number_format($summary['paid'], 0) }}</div><div class="text-[11px] text-stone-500">Paid total</div></div>
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-4"><i data-lucide="clock" class="w-6 h-6 text-sun-400"></i><div class="text-lg font-black mt-1">{{ $currency }} {{ number_format($summary['pending'], 0) }}</div><div class="text-[11px] text-stone-500">Pending</div></div>
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-4"><i data-lucide="x-circle" class="w-6 h-6 text-brand-500"></i><div class="text-lg font-black mt-1">{{ $summary['failed'] }}</div><div class="text-[11px] text-stone-500">Failed</div></div>
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-4"><i data-lucide="ban" class="w-6 h-6 text-stone-400"></i><div class="text-lg font-black mt-1">{{ $summary['cancelled'] }}</div><div class="text-[11px] text-stone-500">Cancelled / timeout</div></div>
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-4"><i data-lucide="smartphone" class="w-6 h-6 text-leaf-600"></i><div class="text-lg font-black mt-1">{{ $summary['mpesa_paid'] }}</div><div class="text-[11px] text-stone-500">M-Pesa paid</div></div>
  <div class="bg-leaf-600 rounded-2xl p-4 text-white"><i data-lucide="banknote" class="w-6 h-6"></i><div class="text-lg font-black mt-1">{{ $currency }} {{ number_format($summary['mpesa_total'], 0) }}</div><div class="text-[11px] text-leaf-100">M-Pesa volume</div></div>
</div>

<div class="flex gap-2 mt-4 text-xs">
  <a href="{{ route('admin.payments.index') }}" class="rounded-full px-4 py-2 border {{ !request('method') ? 'bg-stone-900 text-white' : 'bg-white dark:bg-stone-900' }}">All</a>
  <a href="{{ route('admin.payments.index', ['method' => 'mpesa']) }}" class="rounded-full px-4 py-2 border flex items-center gap-1 {{ request('method') === 'mpesa' ? 'bg-leaf-600 text-white border-leaf-600' : 'bg-white dark:bg-stone-900' }}"><i data-lucide="smartphone" class="w-4 h-4"></i>M-Pesa</a>
  <a href="{{ route('admin.payments.index', ['method' => 'card']) }}" class="rounded-full px-4 py-2 border {{ request('method') === 'card' ? 'bg-stone-900 text-white' : 'bg-white dark:bg-stone-900' }}">Card</a>
  <a href="{{ route('admin.payments.index', ['method' => 'cod']) }}" class="rounded-full px-4 py-2 border {{ request('method') === 'cod' ? 'bg-stone-900 text-white' : 'bg-white dark:bg-stone-900' }}">COD</a>
</div>

<form method="GET" class="flex flex-wrap gap-2 mt-3">
  @if(request('method'))<input type="hidden" name="method" value="{{ request('method') }}">@endif
  <input name="q" value="{{ request('q') }}" placeholder="Search order #, customer, phone, receipt, checkout ID..." class="flex-1 min-w-[220px] border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 rounded-xl px-3 py-2 text-sm">
  <select name="status" class="border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 rounded-xl px-3 py-2 text-sm">
    <option value="">All statuses</option>
    @foreach(['pending','paid','failed','cancelled','timeout','refunded'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach
  </select>
  <button class="flex items-center gap-1 bg-stone-900 dark:bg-white text-white dark:text-stone-900 rounded-xl px-4 py-2 text-sm font-semibold"><i data-lucide="search" class="w-4 h-4"></i>Filter</button>
</form>

<div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden mt-4 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3">Date</th><th class="p-3">Order / Customer</th><th class="p-3">Phone</th><th class="p-3">Method</th><th class="p-3">Amount</th><th class="p-3">Receipt</th><th class="p-3">Status</th><th></th></tr></thead>
    <tbody>
      @forelse($payments as $payment)
        <tr class="border-t border-stone-100 dark:border-stone-800">
          <td class="p-3 whitespace-nowrap text-stone-500">{{ $payment->created_at->format('d M, H:i') }}</td>
          <td class="p-3"><a href="{{ $payment->order ? route('admin.orders.show', $payment->order) : '#' }}" class="font-semibold text-brand-600">{{ $payment->order?->order_number ?? '#' . $payment->order_id }}</a><div class="text-[10px] text-stone-400">{{ $payment->order?->customer_name }}</div></td>
          <td class="p-3 text-xs">{{ $payment->phone ?? $payment->order?->customer_phone ?? '—' }}</td>
          <td class="p-3"><span class="inline-flex items-center gap-1">{{ strtoupper($payment->method) }}@if($payment->method === 'mpesa')<i data-lucide="smartphone" class="w-3.5 h-3.5 text-leaf-600"></i>@endif</span></td>
          <td class="p-3 font-semibold">{{ $currency }} {{ number_format($payment->amount, 2) }}</td>
          <td class="p-3 font-mono text-xs">{{ $payment->receipt_number ?? '—' }}</td>
          <td class="p-3"><span class="text-[10px] font-bold uppercase rounded-full px-2 py-0.5 {{ $payment->status === 'paid' ? 'bg-leaf-100 text-leaf-700' : (in_array($payment->status, ['failed','cancelled','timeout']) ? 'bg-brand-50 text-brand-600' : ($payment->status === 'refunded' ? 'bg-stone-200 text-stone-600' : 'bg-sun-100 text-stone-700')) }}">{{ $payment->status }}</span></td>
          <td class="p-3 text-right whitespace-nowrap">
            <a href="{{ route('admin.payments.show', $payment) }}" class="inline-flex items-center gap-1 text-brand-600 font-semibold" title="View details"><i data-lucide="eye" class="w-4 h-4"></i></a>
            @if($payment->method === 'mpesa' && $payment->status === 'pending')
              <form action="{{ route('admin.payments.query', $payment) }}" method="POST" class="inline">@csrf<button class="inline-flex items-center gap-1 text-leaf-700 dark:text-leaf-100 font-semibold ml-2" title="Query M-Pesa status"><i data-lucide="refresh-cw" class="w-4 h-4"></i></button></form>
            @endif
            @if($payment->status === 'pending')
              <form action="{{ route('admin.payments.verify', $payment) }}" method="POST" class="inline">@csrf @method('PATCH')<button class="inline-flex items-center gap-1 text-leaf-700 dark:text-leaf-100 font-semibold ml-2" title="Verify manually"><i data-lucide="check-circle-2" class="w-4 h-4"></i></button></form>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="8" class="p-6 text-center text-stone-400">No payments found.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $payments->links() }}</div>
@endsection
