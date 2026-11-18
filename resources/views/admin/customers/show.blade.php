@extends('admin.layout')
@section('title', $user->name . ' — Customer — Admin')
@section('content')
<a href="{{ route('admin.customers.index') }}" class="inline-flex items-center gap-1 text-sm text-brand-600 font-semibold"><i data-lucide="arrow-left" class="w-4 h-4"></i>Customers</a>
<div class="grid lg:grid-cols-3 gap-4 mt-3">
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6">
    <div class="flex items-center gap-4">
      <div class="w-16 h-16 rounded-full bg-leaf-100 dark:bg-stone-800 flex items-center justify-center"><i data-lucide="user" class="w-8 h-8 text-leaf-600 dark:text-leaf-100"></i></div>
      <div>
        <h1 class="text-xl font-bold">{{ $user->name }}</h1>
        <p class="text-xs text-stone-500">Member since {{ $user->created_at->format('M Y') }}</p>
      </div>
    </div>
    <div class="border-t border-stone-100 dark:border-stone-800 my-4"></div>
    <dl class="text-sm space-y-2">
      <div class="flex justify-between"><dt class="text-stone-500">Email</dt><dd class="font-medium">{{ $user->email }}</dd></div>
      <div class="flex justify-between"><dt class="text-stone-500">Phone</dt><dd class="font-medium">{{ $user->phone ?? '—' }}</dd></div>
      <div class="flex justify-between"><dt class="text-stone-500">Orders</dt><dd class="font-medium">{{ $user->orders_count }}</dd></div>
      <div class="flex justify-between"><dt class="text-stone-500">Total spent</dt><dd class="font-black">{{ $currency }} {{ number_format($totalSpent, 2) }}</dd></div>
    </dl>
    <form action="{{ route('admin.customers.block', $user) }}" method="POST" class="mt-4">@csrf @method('PATCH')
      <button class="w-full flex items-center justify-center gap-2 rounded-full py-2 font-bold {{ $user->is_blocked ? 'bg-leaf-500 text-white' : 'bg-brand-500 text-white' }}"><i data-lucide="{{ $user->is_blocked ? 'lock-open' : 'ban' }}" class="w-4 h-4"></i>{{ $user->is_blocked ? 'Unblock account' : 'Block account' }}</button>
    </form>
  </div>

  <div class="lg:col-span-2 space-y-4">
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6">
      <h2 class="font-bold flex items-center gap-2 mb-2"><i data-lucide="map-pin" class="w-5 h-5"></i>Delivery addresses</h2>
      @forelse($addresses as $address)
        <p class="text-sm text-stone-600 dark:text-stone-300 py-1 border-b border-stone-100 dark:border-stone-800 last:border-0">{{ $address->shipping_address }}, {{ $address->city }}</p>
      @empty
        <p class="text-sm text-stone-400">No addresses on record.</p>
      @endforelse
    </div>
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden">
      <h2 class="font-bold flex items-center gap-2 p-4 border-b border-stone-100 dark:border-stone-800"><i data-lucide="receipt" class="w-5 h-5"></i>Order history</h2>
      <table class="w-full text-sm">
        <tbody>
          @forelse($orders as $order)
            <tr class="border-t border-stone-100 dark:border-stone-800">
              <td class="p-3"><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-brand-600">{{ $order->order_number }}</a><div class="text-[10px] text-stone-400">{{ $order->created_at->format('d M Y, H:i') }}</div></td>
              <td class="p-3">{{ $currency }} {{ number_format($order->total, 2) }}</td>
              <td class="p-3">{{ ucfirst($order->status) }}</td>
            </tr>
          @empty
            <tr><td class="p-6 text-center text-stone-400">No orders yet.</td></tr>
          @endforelse
        </tbody>
      </table>
      <div class="p-3">{{ $orders->links() }}</div>
    </div>
  </div>
</div>
@endsection
