@extends('admin.layout')
@section('title', 'Homepage Sections — Admin')
@section('content')
<div class="flex items-center justify-between flex-wrap gap-2">
  <div>
    <h1 class="text-2xl font-bold">Homepage sections</h1>
    <p class="text-sm text-stone-500 dark:text-stone-400 mt-1">Enable/disable, rename, re-order and reconfigure the homepage blocks. Hidden automatically when empty.</p>
  </div>
  <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center gap-1 border border-stone-300 dark:border-stone-700 rounded-full px-4 py-2 text-sm font-semibold"><i data-lucide="external-link" class="w-4 h-4"></i>View storefront</a>
</div>

<form method="POST" action="{{ route('admin.homepage-sections.reorder') }}">@csrf
<div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden mt-4 overflow-x-auto">
  <table class="w-full text-sm" id="sections-table">
    <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3 w-10">#</th><th class="p-3">Key</th><th class="p-3">Title</th><th class="p-3">Source</th><th class="p-3">Items</th><th class="p-3">Live</th><th></th></tr></thead>
    <tbody>
      @foreach($sections as $index => $section)
        <tr class="border-t border-stone-100 dark:border-stone-800 section-row" data-id="{{ $section->id }}">
          <td class="p-3 text-stone-400 font-mono text-xs">{{ $index + 1 }}</td>
          <td class="p-3 font-mono text-xs">{{ $section->key }}</td>
          <td class="p-3 font-semibold">{{ $section->title }}@if($section->subtitle)<div class="text-[11px] text-stone-400 font-normal">{{ $section->subtitle }}</div>@endif</td>
          <td class="p-3 text-xs text-stone-500">{{ $section->mode === 'flag' ? 'Flag: ' . $section->value : ($section->mode === 'category' ? 'Category: ' . $section->value : $section->mode) }}</td>
          <td class="p-3">{{ $section->max_items }}</td>
          <td class="p-3"><form action="{{ route('admin.homepage-sections.toggle', $section) }}" method="POST">@csrf @method('PATCH')<button class="text-[10px] font-bold uppercase rounded-full px-3 py-1 {{ $section->is_active ? 'bg-leaf-100 text-leaf-700' : 'bg-stone-200 text-stone-500' }}">{{ $section->is_active ? 'Visible' : 'Hidden' }}</button></form></td>
          <td class="p-3 text-right whitespace-nowrap">
            <input type="hidden" name="order[]" value="{{ $section->id }}" class="order-input">
            <a href="{{ route('admin.homepage-sections.edit', $section) }}" class="text-leaf-700 dark:text-leaf-100 font-semibold inline-flex items-center gap-1"><i data-lucide="pencil" class="w-4 h-4"></i>Edit</a>
            <span class="drag-handle cursor-grab inline-block ml-2 align-middle" title="Drag to reorder"><i data-lucide="grip-vertical" class="w-5 h-5 text-stone-400"></i></span>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
<div class="flex justify-end mt-3"><button class="flex items-center gap-1 bg-stone-900 dark:bg-white text-white dark:text-stone-900 rounded-full px-5 py-2 text-sm font-semibold"><i data-lucide="save" class="w-4 h-4"></i>Save order</button></div>
</form>
<script>
(function () {
  const tbody = document.querySelector('#sections-table tbody');
  let dragged = null;
  tbody.addEventListener('dragstart', e => { dragged = e.target.closest('.section-row'); e.target.closest('.section-row')?.classList.add('opacity-50'); });
  tbody.addEventListener('dragover', e => {
    e.preventDefault();
    const target = e.target.closest('.section-row');
    if (target && dragged && target !== dragged) {
      const rect = target.getBoundingClientRect();
      const after = (e.clientY - rect.top) > rect.height / 2;
      tbody.insertBefore(dragged, after ? target.nextSibling : target);
    }
  });
  tbody.addEventListener('dragend', () => { dragged?.classList.remove('opacity-50'); dragged = null; });
  tbody.querySelectorAll('.section-row').forEach(row => row.draggable = true);
})();
</script>
@endsection
