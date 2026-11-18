@extends('admin.layout')
@section('title', 'Products — Admin')
@section('content')
<div class="flex justify-between items-center gap-4 flex-wrap"><h1 class="text-2xl font-bold">Products</h1><a href="{{ route('admin.products.create') }}" class="flex items-center gap-1 bg-brand-500 text-white font-bold rounded-full px-5 py-2 text-sm hover:bg-brand-600"><i data-lucide="plus" class="w-4 h-4"></i>New product</a></div>

<form method="GET" class="flex flex-wrap gap-2 mt-4">
  <input name="q" value="{{ request('q') }}" placeholder="Search name, SKU, barcode..." class="flex-1 min-w-[180px] border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 rounded-xl px-3 py-2 text-sm">
  <select name="category" class="border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 rounded-xl px-3 py-2 text-sm">
    <option value="">All categories</option>
    @foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>@endforeach
  </select>
  <select name="stock" class="border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 rounded-xl px-3 py-2 text-sm">
    <option value="">All stock</option>
    <option value="low" @selected(request('stock') === 'low')>Low stock only</option>
  </select>
  <button class="flex items-center gap-1 bg-stone-900 dark:bg-white text-white dark:text-stone-900 rounded-xl px-4 py-2 text-sm font-semibold"><i data-lucide="search" class="w-4 h-4"></i>Filter</button>
</form>

<div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden mt-4 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3"></th><th class="p-3">Name</th><th class="p-3">SKU</th><th class="p-3">Category</th><th class="p-3">Price</th><th class="p-3">Stock</th><th class="p-3">Status</th><th class="p-3">Featured</th><th></th></tr></thead>
    <tbody>
      @forelse($products as $product)
        <tr class="border-t border-stone-100 dark:border-stone-800 {{ $product->is_active ? '' : 'opacity-50' }}">
          <td class="p-3">
            <div class="w-12 h-12 rounded-lg bg-leaf-50 dark:bg-stone-800 flex items-center justify-center overflow-hidden">
              @if($product->imageUrl())<img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="h-full w-full object-cover">@else<i data-lucide="package" class="w-6 h-6 text-leaf-600"></i>@endif
            </div>
          </td>
          <td class="p-3 font-semibold">{{ $product->name }}</td>
          <td class="p-3 text-stone-500">{{ $product->sku ?? '—' }}</td>
          <td class="p-3">{{ $product->category->name ?? '—' }}</td>
          <td class="p-3">{{ $currency }} {{ number_format($product->sale_price ?? $product->price, 2) }}</td>
          <td class="p-3 {{ $product->isLowStock() ? 'text-brand-600 font-bold' : '' }}">{{ $product->stock }}</td>
          <td class="p-3"><span class="text-[10px] font-bold uppercase rounded-full px-2 py-0.5 {{ $product->is_active ? 'bg-leaf-100 text-leaf-700' : 'bg-stone-200 text-stone-500' }}">{{ $product->is_active ? 'Active' : 'Disabled' }}</span></td>
          <td class="p-3">@if($product->is_featured)<i data-lucide="star" class="w-5 h-5 text-sun-400 fill-current"></i>@endif</td>
          <td class="p-3 text-right whitespace-nowrap">
            <form action="{{ route('admin.products.toggle', $product) }}" method="POST" class="inline">@csrf @method('PATCH')<button class="text-stone-500 dark:text-stone-400 font-semibold inline-flex items-center gap-1" title="{{ $product->is_active ? 'Disable' : 'Enable' }}"><i data-lucide="{{ $product->is_active ? 'eye-off' : 'eye' }}" class="w-4 h-4"></i></button></form>
            <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex items-center gap-1 text-leaf-700 dark:text-leaf-100 font-semibold ml-2"><i data-lucide="pencil" class="w-4 h-4"></i></a>
            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Delete this product?')">@csrf @method('DELETE')<button class="inline-flex items-center gap-1 text-brand-600 font-semibold ml-2"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form>
          </td>
        </tr>
      @empty
        <tr><td colspan="9" class="p-8 text-center text-stone-400">No products match. <a class="text-brand-600 font-semibold" href="{{ route('admin.products.create') }}">Add your first product</a>.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $products->links() }}</div>
@endsection
