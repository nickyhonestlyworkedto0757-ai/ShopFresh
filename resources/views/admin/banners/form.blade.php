@extends('admin.layout')
@section('title', ($banner->exists ? 'Edit' : 'New') . ' banner — ShopFresh Admin')
@section('content')
<h1 class="text-2xl font-bold">{{ $banner->exists ? 'Edit' : 'New' }} banner</h1>
<p class="text-sm text-stone-500 mt-1">Recommended: 1920 × 280 px (JPG, PNG or WebP, max 4MB). You can upload as many banners as you want — the slider auto-plays through all active ones.</p>
@unless($banner->exists)
<form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="bg-leaf-50 dark:bg-stone-800/50 rounded-2xl border border-leaf-100 dark:border-stone-800 p-4 mt-3 max-w-2xl flex flex-wrap gap-3 items-end">@csrf
  <div class="flex-1 min-w-[200px]"><label class="text-xs font-semibold text-leaf-700 dark:text-leaf-100"><i data-lucide="images" class="inline w-4 h-4"></i> Bulk upload — select multiple images (10, 20, 50...)</label>
    <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="mt-1 text-xs block">
    <p class="text-[11px] text-stone-400 mt-1">Each image becomes a separate active banner, titled from its filename. Reorder afterwards via sort order.</p>
  </div>
  <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Active</label>
  <button class="flex items-center gap-2 bg-leaf-600 text-white font-bold rounded-full px-6 py-2 hover:bg-leaf-700"><i data-lucide="upload" class="w-4 h-4"></i>Upload all</button>
</form>
<div class="text-xs font-bold uppercase tracking-widest text-stone-400 mt-4 max-w-2xl border-b border-stone-100 dark:border-stone-800 pb-2">Or add a single banner</div>
@endunless
<form action="{{ $banner->exists ? route('admin.banners.update', $banner) : route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-stone-200 p-6 mt-4 grid sm:grid-cols-2 gap-4 max-w-2xl">@csrf
  @if($banner->exists) @method('PUT') @endif
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Title (optional)</label><input name="title" value="{{ old('title', $banner->title) }}" placeholder="e.g. Fresh fruits, up to 30% off" class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2"></div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Subtitle (optional)</label><input name="subtitle" value="{{ old('subtitle', $banner->subtitle) }}" placeholder="This week only" class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2"></div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Banner image{{ $banner->exists ? ' (leave empty to keep current)' : '' }}</label>
    <div class="mt-1 flex items-center gap-4">
      <div class="w-40 h-16 rounded-xl bg-leaf-50 flex items-center justify-center overflow-hidden shrink-0">
        @if($banner->exists)<img src="{{ $banner->imageUrl() }}" alt="Current banner" class="h-full w-full object-cover">@else<i data-lucide="image" class="w-8 h-8 text-leaf-600"></i>@endif
      </div>
      <input type="file" name="image" accept="image/jpeg,image/png,image/webp" {{ $banner->exists ? '' : 'required' }} class="text-sm">
    </div>
  </div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Link URL (optional)</label><input name="link_url" value="{{ old('link_url', $banner->link_url) }}" placeholder="/shop?category=fruits" class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500">Sort order</label><input type="number" min="0" name="sort_order" value="{{ old('sort_order', $banner->sort_order ?? 0) }}" class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2"><p class="text-[11px] text-stone-400 mt-1">Lower numbers appear first.</p></div>
  <div class="flex items-end"><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner->is_active ?? true))> Active (visible on homepage)</label></div>
  <div class="sm:col-span-2 flex gap-3"><button class="flex items-center gap-2 bg-leaf-500 text-white font-bold rounded-full px-6 py-2"><i data-lucide="save" class="w-4 h-4"></i>Save</button><a href="{{ route('admin.banners.index') }}" class="px-6 py-2 text-stone-500">Cancel</a></div>
</form>
@endsection
