@extends('admin.layout')
@section('title', 'Notifications — Admin')
@section('content')
<div class="flex items-center justify-between flex-wrap gap-3">
  <h1 class="text-2xl font-bold">Notifications</h1>
  <div class="flex flex-wrap gap-2">
    @if(Auth::guard('admin')->user()->hasPermission('banners'))<a href="{{ route('admin.notifications.send') }}" class="inline-flex items-center gap-1 text-sm bg-brand-500 text-white rounded-full px-4 py-2 font-semibold hover:bg-brand-600"><i data-lucide="send" class="w-4 h-4"></i>Send</a>
    <a href="{{ route('admin.notifications.history') }}" class="inline-flex items-center gap-1 text-sm border border-stone-300 dark:border-stone-700 rounded-full px-4 py-2 font-semibold"><i data-lucide="history" class="w-4 h-4"></i>History</a>
    <a href="{{ route('admin.notifications.templates') }}" class="inline-flex items-center gap-1 text-sm border border-stone-300 dark:border-stone-700 rounded-full px-4 py-2 font-semibold"><i data-lucide="layout-template" class="w-4 h-4"></i>Templates</a>@endif
    <form action="{{ route('admin.notifications.readAll') }}" method="POST">@csrf<button class="inline-flex items-center gap-1 text-sm border border-stone-300 dark:border-stone-700 rounded-full px-4 py-2 font-semibold"><i data-lucide="check-check" class="w-4 h-4"></i>Mark all read</button></form>
    <form action="{{ route('admin.notifications.clear') }}" method="POST" onsubmit="return confirm('Clear all notifications?')">@csrf @method('DELETE')<button class="inline-flex items-center gap-1 text-sm border border-stone-300 dark:border-stone-700 rounded-full px-4 py-2 font-semibold text-brand-600"><i data-lucide="trash-2" class="w-4 h-4"></i>Clear all</button></form>
  </div>
</div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-4"><div class="text-xs text-stone-500">Unread inbox</div><div class="text-2xl font-black">{{ $stats['unread'] }}</div></div>
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-4"><div class="text-xs text-stone-500">Delivered</div><div class="text-2xl font-black text-leaf-600">{{ number_format($stats['sent']) }}</div></div>
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-4"><div class="text-xs text-stone-500">Failed</div><div class="text-2xl font-black text-brand-600">{{ number_format($stats['failed']) }}</div></div>
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-4"><div class="text-xs text-stone-500">Est. SMS cost</div><div class="text-2xl font-black">{{ number_format($stats['cost'], 2) }}</div></div>
</div>
<div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden mt-4">
  @forelse($notifications as $notification)
    <a href="{{ route('admin.notifications.open', $notification) }}" class="flex items-start gap-3 p-4 border-b border-stone-100 dark:border-stone-800 last:border-0 hover:bg-stone-50 dark:hover:bg-stone-800/40 {{ $notification->read_at ? 'opacity-60' : '' }}">
      <i data-lucide="{{ match($notification->type) { 'new_order' => 'receipt', 'new_customer' => 'user-plus', 'low_stock' => 'alert-triangle', 'payment' => 'banknote', default => 'bell' } }}" class="w-5 h-5 mt-0.5 {{ $notification->type === 'low_stock' ? 'text-sun-400' : 'text-leaf-600' }}"></i>
      <div class="flex-1 min-w-0">
        <div class="text-sm font-semibold">{{ $notification->title }}</div>
        @if($notification->body)<div class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">{{ $notification->body }}</div>@endif
        <div class="text-[10px] text-stone-400 mt-1">{{ $notification->created_at->diffForHumans() }}</div>
      </div>
      @unless($notification->read_at)<span class="w-2.5 h-2.5 rounded-full bg-brand-500 mt-2"></span>@endunless
    </a>
  @empty
    <div class="p-12 text-center text-stone-400">
      <i data-lucide="bell-off" class="w-12 h-12 mx-auto mb-3"></i>
      <p>No notifications yet.</p>
    </div>
  @endforelse
</div>
<div class="mt-4">{{ $notifications->links() }}</div>
@endsection
