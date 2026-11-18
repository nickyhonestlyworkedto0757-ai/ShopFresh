@extends('admin.layout')
@section('title', 'Categories — Admin')
@section('content')
<div class="flex justify-between items-center gap-4"><h1 class="text-2xl font-bold">Categories</h1><a href="{{ route('admin.categories.create') }}" class="flex items-center gap-1 bg-brand-500 text-white font-bold rounded-full px-5 py-2 text-sm hover:bg-brand-600"><i data-lucide="plus" class="w-4 h-4"></i>New category</a></div>
<div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden mt-4 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3"></th><th class="p-3">Name</th><th class="p-3">Type</th><th class="p-3">Icon</th><th class="p-3">Products</th><th class="p-3">Subcategories</th><th class="p-3">Order</th><th></th></tr></thead>
    <tbody>
      @forelse($categories as $category)
        <tr class="border-t border-stone-100 dark:border-stone-800">
          <td class="p-3">
            <div class="w-12 h-12 rounded-lg bg-leaf-50 dark:bg-stone-800 flex items-center justify-center overflow-hidden">
              @if($category->imageUrl())<img src="{{ $category->imageUrl() }}" alt="{{ $category->name }}" class="h-full w-full object-cover">@elseif($category->icon)<i data-lucide="{{ $category->icon }}" class="w-6 h-6 text-leaf-600"></i>@else<i data-lucide="tags" class="w-6 h-6 text-leaf-600"></i>@endif
            </div>
          </td>
          <td class="p-3 font-semibold">{{ $category->name }}<div class="text-[11px] text-stone-400 font-normal">{{ $category->description }}</div></td>
          <td class="p-3">{{ $category->parent_id ? 'Subcategory of ' . ($category->parent->name ?? '') : 'Main category' }}</td>
          <td class="p-3 text-stone-500">{{ $category->icon ?? '—' }}</td>
          <td class="p-3">{{ $category->products_count }}</td>
          <td class="p-3">{{ $category->subcategories_count }}</td>
          <td class="p-3">{{ $category->sort_order }}</td>
          <td class="p-3 text-right whitespace-nowrap">
            <a href="{{ route('admin.categories.edit', $category) }}" class="inline-flex items-center gap-1 text-leaf-700 dark:text-leaf-100 font-semibold"><i data-lucide="pencil" class="w-4 h-4"></i>Edit</a>
            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category?')">@csrf @method('DELETE')<button class="inline-flex items-center gap-1 text-brand-600 font-semibold ml-3"><i data-lucide="trash-2" class="w-4 h-4"></i>Delete</button></form>
          </td>
        </tr>
      @empty
        <tr><td colspan="8" class="p-8 text-center text-stone-400">No categories yet. <a class="text-brand-600 font-semibold" href="{{ route('admin.categories.create') }}">Create one</a>.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $categories->links() }}</div>
@endsection
