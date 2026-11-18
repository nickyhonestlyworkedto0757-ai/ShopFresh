@extends('admin.layout')
@section('title', 'Notification templates — Admin')
@section('content')
<div class="flex items-center justify-between flex-wrap gap-2">
  <div><h1 class="text-2xl font-bold">Notification templates</h1><p class="text-sm text-stone-500 dark:text-stone-400">Reusable messages with placeholders: @{{name}}, @{{order_number}}, @{{product_name}}, @{{total}}, @{{link}}</p></div>
  <a href="{{ route('admin.notifications.templates.create') }}" class="flex items-center gap-1 bg-brand-500 text-white font-bold rounded-full px-5 py-2 text-sm"><i data-lucide="plus" class="w-4 h-4"></i>New template</a>
</div>
<div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden mt-4 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3">Name</th><th class="p-3">Key</th><th class="p-3">Channel</th><th class="p-3">Subject</th><th class="p-3">Active</th><th></th></tr></thead>
    <tbody>
      @forelse($templates as $template)
        <tr class="border-t border-stone-100 dark:border-stone-800"><td class="p-3 font-semibold">{{ $template->name }}</td><td class="p-3 font-mono text-xs">{{ $template->key }}</td><td class="p-3 uppercase">{{ $template->channel }}</td><td class="p-3 max-w-xs truncate">{{ $template->subject }}</td><td class="p-3">{{ $template->is_active ? 'Yes' : 'No' }}</td><td class="p-3 text-right whitespace-nowrap"><a href="{{ route('admin.notifications.templates.edit', $template) }}" class="text-leaf-700 dark:text-leaf-100 font-semibold"><i data-lucide="pencil" class="w-4 h-4"></i></a><form action="{{ route('admin.notifications.templates.destroy', $template) }}" method="POST" class="inline" onsubmit="return confirm('Delete template?')">@csrf @method('DELETE')<button class="text-brand-600 ml-2"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form></td></tr>
      @empty
        <tr><td colspan="6" class="p-8 text-center text-stone-400">No templates yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
