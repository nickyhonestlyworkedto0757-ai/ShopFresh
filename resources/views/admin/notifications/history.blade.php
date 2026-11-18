@extends('admin.layout')
@section('title', 'Notification history — Admin')
@section('content')
<a href="{{ route('admin.notifications.index') }}" class="inline-flex items-center gap-1 text-sm text-brand-600 font-semibold"><i data-lucide="arrow-left" class="w-4 h-4"></i>Notifications</a>
<h1 class="text-2xl font-bold mt-1">Notification history</h1>
<form method="GET" class="flex flex-wrap gap-2 mt-3">
  <input name="q" value="{{ request('q') }}" placeholder="Recipient or subject..." class="flex-1 min-w-[180px] border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 rounded-xl px-3 py-2 text-sm">
  <select name="channel" class="border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 rounded-xl px-3 py-2 text-sm"><option value="">All channels</option><option value="email" @selected(request('channel') === 'email')>Email</option><option value="sms" @selected(request('channel') === 'sms')>SMS</option></select>
  <select name="status" class="border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 rounded-xl px-3 py-2 text-sm"><option value="">All statuses</option><option value="sent" @selected(request('status') === 'sent')>Sent</option><option value="failed" @selected(request('status') === 'failed')>Failed</option><option value="skipped" @selected(request('status') === 'skipped')>Skipped</option></select>
  <select name="type" class="border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 rounded-xl px-3 py-2 text-sm"><option value="">All types</option>@foreach(['welcome','otp','order_placed','order_paid','order_failed','order_confirmed','order_processing','order_shipped','order_out_for_delivery','order_delivered','order_cancelled','new_product','promotion','custom'] as $t)<option value="{{ $t }}" @selected(request('type') === $t)>{{ str_replace('_', ' ', ucfirst($t)) }}</option>@endforeach</select>
  <button class="flex items-center gap-1 bg-stone-900 dark:bg-white text-white dark:text-stone-900 rounded-xl px-4 py-2 text-sm font-semibold"><i data-lucide="search" class="w-4 h-4"></i>Filter</button>
</form>
<div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden mt-4 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3">When</th><th class="p-3">Channel</th><th class="p-3">Recipient</th><th class="p-3">Type</th><th class="p-3">Status</th><th class="p-3">Attempts</th><th class="p-3">Error</th><th></th></tr></thead>
    <tbody>
      @forelse($logs as $log)
        <tr class="border-t border-stone-100 dark:border-stone-800" title="{{ $log->body }}"><td class="p-3 text-xs text-stone-500 whitespace-nowrap">{{ $log->created_at->format('d M, H:i') }}</td><td class="p-3 uppercase">{{ $log->channel }}</td><td class="p-3 font-mono text-xs">{{ $log->recipient }}</td><td class="p-3">{{ str_replace('_', ' ', $log->type) }}</td><td class="p-3"><span class="text-[10px] font-bold uppercase rounded-full px-2 py-0.5 {{ $log->status === 'sent' ? 'bg-leaf-100 text-leaf-700' : ($log->status === 'failed' ? 'bg-brand-50 text-brand-600' : 'bg-stone-200 text-stone-500') }}">{{ $log->status }}</span></td><td class="p-3">{{ $log->attempts }}</td><td class="p-3 text-xs text-stone-400 max-w-xs truncate">{{ $log->error }}</td><td class="p-3 text-right">
          @if($log->status === 'failed')<form action="{{ route('admin.notifications.retry', $log) }}" method="POST" class="inline">@csrf<button class="inline-flex items-center gap-1 text-leaf-700 dark:text-leaf-100 font-semibold"><i data-lucide="refresh-cw" class="w-4 h-4"></i>Retry</button></form>@endif
        </td></tr>
      @empty
        <tr><td colspan="8" class="p-6 text-center text-stone-400">No notifications match.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
