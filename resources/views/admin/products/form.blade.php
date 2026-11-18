@extends('admin.layout')
@section('title', ($product->exists ? 'Edit' : 'New') . ' product — Admin')
@section('content')
<h1 class="text-2xl font-bold">{{ $product->exists ? 'Edit' : 'New' }} product</h1>
<form action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6 mt-4 grid sm:grid-cols-2 gap-4">@csrf
  @if($product->exists) @method('PUT') @endif

  <div class="sm:col-span-2 text-xs font-bold uppercase tracking-widest text-stone-400 border-b border-stone-100 dark:border-stone-800 pb-2">Basic information</div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Name *</label><input name="name" required value="{{ old('name', $product->name) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Category *</label><select name="category_id" required class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2">@foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->parent_id ? '— ' : '' }}{{ $cat->name }}</option>@endforeach</select></div>
  <div><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">SKU</label><input name="sku" value="{{ old('sku', $product->sku) }}" placeholder="SKU-001" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Barcode</label><input name="barcode" value="{{ old('barcode', $product->barcode) }}" placeholder="EAN-13" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Featured</label><label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))> Show on homepage</label></div>
  <div><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Status</label><label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))> Active (visible in store)</label></div>
  <div><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Best seller</label><label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="is_best_seller" value="1" @checked(old('is_best_seller', $product->is_best_seller))> <i data-lucide="flame" class="w-4 h-4 text-brand-500"></i> Best selling items</label></div>
  <div><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Sponsored</label><label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="is_sponsored" value="1" @checked(old('is_sponsored', $product->is_sponsored))> <i data-lucide="megaphone" class="w-4 h-4 text-sun-400"></i> Sponsored placement</label></div>
  <div><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Official store</label><label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="is_official_store" value="1" @checked(old('is_official_store', $product->is_official_store))> <i data-lucide="store" class="w-4 h-4 text-leaf-600"></i> Official store deal</label></div>
  <div><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Flash sale ends at</label><input type="datetime-local" name="flash_sale_ends_at" value="{{ old('flash_sale_ends_at', $product->flash_sale_ends_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  @unless($product->exists)<label class="sm:col-span-2 flex items-center gap-2 text-sm bg-sun-100 dark:bg-sun-400/20 rounded-xl px-3 py-2"><input type="checkbox" name="notify_customers" value="1" checked> <span><b>Notify subscribed customers</b> about this product (by email/SMS based on their preferences)</span></label>@endunless

  <div class="sm:col-span-2 text-xs font-bold uppercase tracking-widest text-stone-400 border-b border-stone-100 dark:border-stone-800 pb-2 pt-2">Pricing & inventory</div>
  <div><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Price ({{ $currency }}) *</label><input type="number" step="0.01" min="0" name="price" required value="{{ old('price', $product->price) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Sale price</label><input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Cost price (for profit report)</label><input type="number" step="0.01" min="0" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div></div>
  <div><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Stock *</label><input type="number" min="0" name="stock" required value="{{ old('stock', $product->stock ?? 0) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Low-stock threshold</label><input type="number" min="0" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>

  <div class="sm:col-span-2 text-xs font-bold uppercase tracking-widest text-stone-400 border-b border-stone-100 dark:border-stone-800 pb-2 pt-2">Content</div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Description</label><textarea name="description" rows="2" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2">{{ old('description', $product->description) }}</textarea></div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Specifications (one per line, e.g. "Weight: 1kg")</label><textarea name="specifications" rows="3" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2">{{ old('specifications', $product->specifications) }}</textarea></div>

  <div class="sm:col-span-2 text-xs font-bold uppercase tracking-widest text-stone-400 border-b border-stone-100 dark:border-stone-800 pb-2 pt-2">Images</div>
  <div class="sm:col-span-2 grid sm:grid-cols-2 gap-4">
    <div>
      <label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Main image</label>
      <div class="mt-1 flex items-center gap-4">
        <div class="w-20 h-20 rounded-xl bg-leaf-50 dark:bg-stone-800 flex items-center justify-center overflow-hidden shrink-0">
          @if($product->imageUrl())<img src="{{ $product->imageUrl() }}" alt="Main image" class="h-full w-full object-cover">@else<i data-lucide="image" class="w-8 h-8 text-leaf-600"></i>@endif
        </div>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="text-xs">
      </div>
    </div>
    <div>
      <label class="text-xs font-semibold text-stone-500 dark:text-stone-400">Gallery images (multiple)</label>
      <input type="file" name="gallery[]" accept="image/jpeg,image/png,image/webp" multiple class="mt-2 text-xs">
      @if($product->exists && $product->images->isNotEmpty())
        <div class="flex gap-2 mt-2 flex-wrap">
          @foreach($product->images as $image)
            <div class="relative">
              <img src="{{ $image->url() }}" class="w-16 h-16 object-cover rounded-lg border border-stone-200 dark:border-stone-700" alt="Gallery image">
              <form action="{{ route('admin.products.images.destroy', [$product, $image]) }}" method="POST" class="absolute -top-2 -right-2" onsubmit="return confirm('Remove image?')">@csrf @method('DELETE')<button class="bg-brand-500 text-white rounded-full w-5 h-5 flex items-center justify-center"><i data-lucide="x" class="w-3 h-3"></i></button></form>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>

  <div class="sm:col-span-2 text-xs font-bold uppercase tracking-widest text-stone-400 border-b border-stone-100 dark:border-stone-800 pb-2 pt-2 flex items-center justify-between">Variations (size / color)
    <button type="button" id="add-variation" class="flex items-center gap-1 text-[11px] text-leaf-700 dark:text-leaf-100 font-semibold normal-case"><i data-lucide="plus-circle" class="w-4 h-4"></i>Add variation</button>
  </div>
  <div class="sm:col-span-2" id="variations-wrap">
    @php $variationRows = old('variations', $product->exists ? $product->variations->map(fn ($v) => ['type' => $v->type, 'value' => $v->value, 'price_delta' => $v->price_delta, 'stock' => $v->stock, 'sku' => $v->sku])->toArray() : []); @endphp
    @foreach($variationRows as $i => $v)
      <div class="variation-row grid grid-cols-2 sm:grid-cols-6 gap-2 mb-2">
        <input name="variations[{{ $i }}][type]" required placeholder="Type (Size)" value="{{ $v['type'] }}" class="border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm">
        <input name="variations[{{ $i }}][value]" required placeholder="Value (M)" value="{{ $v['value'] }}" class="border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm">
        <input type="number" step="0.01" name="variations[{{ $i }}][price_delta]" placeholder="+Price" value="{{ $v['price_delta'] }}" class="border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm">
        <input type="number" min="0" name="variations[{{ $i }}][stock]" placeholder="Stock" value="{{ $v['stock'] }}" class="border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm">
        <input name="variations[{{ $i }}][sku]" placeholder="SKU" value="{{ $v['sku'] }}" class="border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm">
        <button type="button" class="remove-variation flex items-center justify-center text-brand-600 border border-brand-100 dark:border-stone-700 rounded-xl"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
      </div>
    @endforeach
  </div>

  <div class="sm:col-span-2 flex gap-3 pt-2 border-t border-stone-100 dark:border-stone-800"><button class="flex items-center gap-2 bg-leaf-500 text-white font-bold rounded-full px-6 py-2 hover:bg-leaf-600"><i data-lucide="save" class="w-4 h-4"></i>Save product</button><a href="{{ route('admin.products.index') }}" class="px-6 py-2 text-stone-500">Cancel</a></div>
</form>
<script>
let variationIndex = {{ count($variationRows) }};
const wrap = document.getElementById('variations-wrap');
document.getElementById('add-variation').addEventListener('click', () => {
  const row = document.createElement('div');
  row.className = 'variation-row grid grid-cols-2 sm:grid-cols-6 gap-2 mb-2';
  row.innerHTML = `
    <input name="variations[${variationIndex}][type]" required placeholder="Type (Size)" class="border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm">
    <input name="variations[${variationIndex}][value]" required placeholder="Value (M)" class="border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm">
    <input type="number" step="0.01" name="variations[${variationIndex}][price_delta]" placeholder="+Price" class="border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm">
    <input type="number" min="0" name="variations[${variationIndex}][stock]" placeholder="Stock" value="0" class="border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm">
    <input name="variations[${variationIndex}][sku]" placeholder="SKU" class="border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm">
    <button type="button" class="remove-variation flex items-center justify-center text-brand-600 border border-stone-200 dark:border-stone-700 rounded-xl"><i data-lucide="trash-2" class="w-4 h-4"></i></button>`;
  wrap.appendChild(row);
  lucide.createIcons();
  variationIndex++;
});
wrap.addEventListener('click', (e) => {
  const btn = e.target.closest('.remove-variation');
  if (btn) btn.closest('.variation-row').remove();
});
</script>
@endsection
