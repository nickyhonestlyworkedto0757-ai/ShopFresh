@extends('admin.layout')
@section('title', 'Activity Log — Admin')
@section('content')
<h1 class="text-2xl font-bold">Activity log</h1>
<div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden mt-4 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3">When</th><th class="p-3">User</th><th class="p-3">Action</th><th class="p-3">Subject</th><th class="p-3">Details</th><th class="p-3">IP</th></tr></thead>
    <tbody>
      @forelse($logs as $log)
        <tr class="border-t border-stone-100 dark:border-stone-800">
          <td class="p-3 whitespace-nowrap text-stone-500">{{ $log->created_at->format('d M, H:i') }}</td>
          <td class="p-3">{{ $log->user?->name ?? 'System' }}</td>
          <td class="p-3"><code class="text-xs bg-stone-100 dark:bg-stone-800 rounded px-1.5 py-0.5">{{ $log->action }}</code></td>
          <td class="p-3">{{ $log->subject ?? '—' }}</td>
          <td class="p-3 text-stone-500 max-w-xs truncate">{{ $log->description }}</td>
          <td class="p-3 text-xs text-stone-400">{{ $log->ip }}</td>
        </tr>
      @empty
        <tr><td colspan="6" class="p-6 text-center text-stone-400">No activity recorded yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
