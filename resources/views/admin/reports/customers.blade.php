@extends('admin.layout')
@section('title', 'Customer report — Admin')
@section('content')
<h1 class="text-2xl font-bold">Customer report</h1>
<div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden mt-4 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3">Customer</th><th class="p-3">Orders</th><th class="p-3 text-right">Total spent</th><th></th></tr></thead>
    <tbody>
      @forelse($customers as $customer)
        <tr class="border-t border-stone-100 dark:border-stone-800"><td class="p-3"><div class="font-medium">{{ $customer->name }}</div><div class="text-[11px] text-stone-400">{{ $customer->email }}</div></td><td class="p-3">{{ $customer->orders_count }}</td><td class="p-3 text-right font-semibold">{{ $currency }} {{ number_format($customer->total_spent ?? 0, 2) }}</td><td class="p-3 text-right"><a href="{{ route('admin.customers.show', $customer) }}" class="text-brand-600"><i data-lucide="arrow-right" class="w-4 h-4"></i></a></td></tr>
      @empty
        <tr><td colspan="4" class="p-6 text-center text-stone-400">No customers yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $customers->links() }}</div>
@endsection
