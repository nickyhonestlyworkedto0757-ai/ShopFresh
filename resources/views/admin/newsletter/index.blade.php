@extends('admin.layout')
@section('title', 'Newsletter — Admin')
@section('content')
<div class="flex items-center justify-between gap-4 flex-wrap">
  <div><h1 class="text-2xl font-bold">Newsletter subscribers</h1><p class="text-sm text-stone-500 dark:text-stone-400">{{ number_format($total) }} total subscribers</p></div>
  <a href="{{ route('admin.newsletter.csv') }}" class="inline-flex items-center gap-1 border border-stone-300 dark:border-stone-700 rounded-full px-4 py-2 text-sm font-semibold"><i data-lucide="file-spreadsheet" class="w-4 h-4"></i>Export CSV</a>
</div>
<div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden mt-4 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3">#</th><th class="p-3">Email</th><th class="p-3">Subscribed</th></tr></thead>
    <tbody>
      @forelse($subscribers as $i => $subscriber)
        <tr class="border-t border-stone-100 dark:border-stone-800">
          <td class="p-3 text-stone-400">{{ $subscribers->firstItem() + $i }}</td>
          <td class="p-3 font-medium">{{ $subscriber->email }}</td>
          <td class="p-3">{{ $subscriber->created_at->format('d M Y, H:i') }}</td>
        </tr>
      @empty
        <tr><td colspan="3" class="p-8 text-center text-stone-400">No subscribers yet. They will appear here when customers subscribe from the storefront footer.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $subscribers->links() }}</div>
@endsection
