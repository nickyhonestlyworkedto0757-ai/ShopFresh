@extends('admin.layout')
@section('title', ($category->exists ? 'Edit' : 'New') . ' category — Admin')
@section('content')
<h1 class="text-2xl font-bold">{{ $category->exists ? 'Edit' : 'New' }} category</h1>
<form action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6 mt-4 grid sm:grid-cols-2 gap-4 max-w-3xl">@csrf
  @if($category->exists) @method('PUT') @endif
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Name *</label><input name="name" required value="{{ old('name', $category->name) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Parent category (optional, for subcategory)</label><select name="parent_id" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"><option value="">— None (top level) —</option>@foreach($parents as $parent)<option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>@endforeach</select></div>
  <div><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Lucide icon name</label><input name="icon" value="{{ old('icon', $category->icon) }}" placeholder="apple, carrot, milk..." class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"><p class="text-[11px] text-stone-400 mt-1">Any <a href="https://lucide.dev/icons" target="_blank" class="underline">lucide.dev</a> icon name, e.g. "apple".</p></div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Description (optional)</label><textarea name="description" rows="2" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2">{{ old('description', $category->description) }}</textarea></div>
  <div><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Display order</label><input type="number" min="0" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Image (optional)</label>
    <div class="mt-1 flex items-center gap-4">
      <div class="w-16 h-16 rounded-xl bg-leaf-50 dark:bg-stone-800 flex items-center justify-center overflow-hidden shrink-0">
        @if($category->imageUrl())<img src="{{ $category->imageUrl() }}" alt="Category image" class="h-full w-full object-cover">@else<i data-lucide="image" class="w-7 h-7 text-leaf-600"></i>@endif
      </div>
      <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="text-xs">
    </div>
  </div>
  <div class="sm:col-span-2 flex gap-3"><button class="flex items-center gap-2 bg-leaf-500 text-white font-bold rounded-full px-6 py-2 hover:bg-leaf-600"><i data-lucide="save" class="w-4 h-4"></i>Save</button><a href="{{ route('admin.categories.index') }}" class="px-6 py-2 text-stone-500">Cancel</a></div>
</form>
@endsection
