@extends('admin.layout')
@section('title', 'Customers — Admin')
@section('content')
<h1 class="text-2xl font-bold">Customers</h1>
<form method="GET" class="flex flex-wrap gap-2 mt-3">
  <input name="q" value="{{ request('q') }}" placeholder="Search name, email, phone..." class="flex-1 min-w-[200px] border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 rounded-xl px-3 py-2 text-sm">
  <select name="status" class="border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 rounded-xl px-3 py-2 text-sm">
    <option value="">All customers</option>
    <option value="active" @selected(request('status') === 'active')>Active</option>
    <option value="blocked" @selected(request('status') === 'blocked')>Blocked</option>
  </select>
  <button class="flex items-center gap-1 bg-stone-900 dark:bg-white text-white dark:text-stone-900 rounded-xl px-4 py-2 text-sm font-semibold"><i data-lucide="search" class="w-4 h-4"></i>Filter</button>
</form>
<div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden mt-4 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3">Customer</th><th class="p-3">Phone</th><th class="p-3">Orders</th><th class="p-3">Total spent</th><th class="p-3">Status</th><th></th></tr></thead>
    <tbody>
      @forelse($customers as $customer)
        <tr class="border-t border-stone-100 dark:border-stone-800 {{ $customer->is_blocked ? 'opacity-60' : '' }}">
          <td class="p-3"><div class="font-semibold">{{ $customer->name }}</div><div class="text-[11px] text-stone-400">{{ $customer->email }}</div></td>
          <td class="p-3">{{ $customer->phone ?? '—' }}</td>
          <td class="p-3">{{ $customer->orders_count }}</td>
          <td class="p-3">{{ $currency }} {{ number_format($customer->total_spent ?? 0, 2) }}</td>
          <td class="p-3"><span class="text-[10px] font-bold uppercase rounded-full px-2 py-0.5 {{ $customer->is_blocked ? 'bg-brand-50 text-brand-600' : 'bg-leaf-100 text-leaf-700' }}">{{ $customer->is_blocked ? 'Blocked' : 'Active' }}</span></td>
          <td class="p-3 text-right whitespace-nowrap">
            <a href="{{ route('admin.customers.show', $customer) }}" class="inline-flex items-center gap-1 text-leaf-700 dark:text-leaf-100 font-semibold"><i data-lucide="eye" class="w-4 h-4"></i></a>
            <form action="{{ route('admin.customers.block', $customer) }}" method="POST" class="inline">@csrf @method('PATCH')<button class="inline-flex items-center gap-1 font-semibold ml-2 {{ $customer->is_blocked ? 'text-leaf-700 dark:text-leaf-100' : 'text-brand-600' }}" title="{{ $customer->is_blocked ? 'Unblock' : 'Block' }}"><i data-lucide="{{ $customer->is_blocked ? 'lock-open' : 'ban' }}" class="w-4 h-4"></i></button></form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="p-6 text-center text-stone-400">No customers found.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $customers->links() }}</div>
@endsection
