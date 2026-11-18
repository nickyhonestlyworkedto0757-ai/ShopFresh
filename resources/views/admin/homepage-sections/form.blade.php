@extends('admin.layout')
@section('title', 'Edit section — Admin')
@section('content')
<a href="{{ route('admin.homepage-sections.index') }}" class="inline-flex items-center gap-1 text-sm text-brand-600 font-semibold"><i data-lucide="arrow-left" class="w-4 h-4"></i>Sections</a>
<h1 class="text-2xl font-bold mt-1">Edit section: <code class="font-mono text-brand-600">{{ $section->key }}</code></h1>
<form action="{{ route('admin.homepage-sections.update', $section) }}" method="POST" class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6 mt-4 grid sm:grid-cols-2 gap-4 max-w-3xl">@csrf @method('PUT')
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Title</label><input name="title" required value="{{ old('title', $section->title) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Subtitle (optional)</label><input name="subtitle" value="{{ old('subtitle', $section->subtitle) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500">Source mode</label><select name="mode" id="section-mode" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2">
    <option value="flag" @selected($section->mode === 'flag')>Flag (best sellers, flash sales, etc.)</option>
    <option value="category" @selected($section->mode === 'category')>Category</option>
    <option value="categories" @selected($section->mode === 'categories')>Browse Top Categories (grid)</option>
    <option value="promo" @selected($section->mode === 'promo')>Promo strip (CTA banner)</option>
  </select></div>
  <div><label class="text-xs font-semibold text-stone-500">Flag / Category</label>
    <div id="flag-select" class="{{ $section->mode === 'category' ? 'hidden' : '' }}">
      <select name="value" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2">
        @foreach(['recent' => 'Recently added (new arrivals)', 'flash_sale' => 'Flash sale (products with active flash sale)', 'best_seller' => 'Best sellers', 'sponsored' => 'Sponsored products', 'official_store' => 'Official stores', 'featured' => 'Featured products'] as $value => $label)
          <option value="{{ $value }}" @selected($section->value === $value)>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div id="category-select" class="{{ $section->mode === 'category' ? '' : 'hidden' }}">
      <select name="value" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2">
        <option value="">— select category —</option>
        @foreach($categories as $cat)<option value="{{ $cat->slug }}" @selected($section->value === $cat->slug)>{{ $cat->name }}</option>@endforeach
      </select>
    </div>
  </div>
  <div><label class="text-xs font-semibold text-stone-500">Max products to show</label><input type="number" min="4" max="60" name="max_items" required value="{{ old('max_items', $section->max_items) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div class="flex items-end"><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $section->is_active))> Visible on homepage</label></div>
  <div class="sm:col-span-2 flex gap-3"><button class="flex items-center gap-2 bg-leaf-500 text-white font-bold rounded-full px-6 py-2 hover:bg-leaf-600"><i data-lucide="save" class="w-4 h-4"></i>Save section</button><a href="{{ route('admin.homepage-sections.index') }}" class="px-6 py-2 text-stone-500">Cancel</a></div>
</form>
<script>
  const mode = document.getElementById('section-mode');
  const flagBlock = document.getElementById('flag-select');
  const catBlock = document.getElementById('category-select');
  const toggle = () => { const isCat = mode.value === 'category'; flagBlock.classList.toggle('hidden', isCat); catBlock.classList.toggle('hidden', !isCat); };
  mode.addEventListener('change', toggle);
</script>
@endsection
