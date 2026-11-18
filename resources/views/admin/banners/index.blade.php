@extends('admin.layout')
@section('title', 'Banners — ShopFresh Admin')
@section('content')
<div class="flex justify-between items-center gap-4"><h1 class="text-2xl font-bold">Banners <span class="text-base font-semibold text-stone-400">({{ $banners->total() }} total)</span></h1><a href="{{ route('admin.banners.create') }}" class="flex items-center gap-1 bg-brand-500 text-white font-bold rounded-full px-5 py-2 text-sm"><i data-lucide="plus" class="w-4 h-4"></i>New banner</a></div>
<p class="text-sm text-stone-500 mt-1">Banners appear as an auto-playing slider on the storefront homepage. Only active banners are shown.</p>
<div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4 mt-4">
  @forelse($banners as $banner)
    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden {{ $banner->is_active ? '' : 'opacity-60' }}">
      <div class="h-36 overflow-hidden bg-leaf-50"><img src="{{ $banner->imageUrl() }}" alt="{{ $banner->title }}" class="h-full w-full object-cover"></div>
      <div class="p-4">
        <div class="flex items-start justify-between gap-2">
          <div>
            <div class="font-bold leading-snug">{{ $banner->title ?: 'Untitled banner' }}</div>
            @if($banner->subtitle)<div class="text-xs text-stone-500 mt-0.5">{{ $banner->subtitle }}</div>@endif
            <div class="text-[11px] text-stone-400 mt-1">Sort order: {{ $banner->sort_order }}</div>
          </div>
          @if($banner->is_active)<span class="flex items-center gap-1 text-xs font-semibold text-leaf-700"><i data-lucide="check-circle-2" class="w-4 h-4"></i>Live</span>@else<span class="flex items-center gap-1 text-xs font-semibold text-stone-400"><i data-lucide="eye-off" class="w-4 h-4"></i>Hidden</span>@endif
        </div>
        <div class="flex items-center justify-end gap-3 mt-3 text-sm">
          <a href="{{ route('admin.banners.edit', $banner) }}" class="inline-flex items-center gap-1 text-leaf-700 font-semibold"><i data-lucide="pencil" class="w-4 h-4"></i>Edit</a>
          <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" onsubmit="return confirm('Delete this banner?')">@csrf @method('DELETE')<button class="inline-flex items-center gap-1 text-brand-600 font-semibold"><i data-lucide="trash-2" class="w-4 h-4"></i>Delete</button></form>
        </div>
      </div>
    </div>
  @empty
    <div class="sm:col-span-2 xl:col-span-3 bg-white rounded-2xl border border-dashed border-stone-300 p-12 text-center text-stone-400">
      <i data-lucide="images" class="w-12 h-12 mx-auto mb-3"></i>
      <p>No banners yet. <a href="{{ route('admin.banners.create') }}" class="text-brand-600 font-semibold">Create your first banner</a>.</p>
    </div>
  @endforelse
</div>
<div class="mt-4">{{ $banners->links() }}</div>
@endsection
