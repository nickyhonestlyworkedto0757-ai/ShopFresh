@extends('admin.layout')
@section('title', 'Product performance — Admin')
@section('content')
<h1 class="text-2xl font-bold">Product performance</h1>
<div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden mt-4 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3">Product</th><th class="p-3">Category</th><th class="p-3">Stock</th><th class="p-3">Units sold</th><th class="p-3 text-right">Revenue</th></tr></thead>
    <tbody>
      @foreach($products as $product)
        @php $stat = $sales[$product->id] ?? null; @endphp
        <tr class="border-t border-stone-100 dark:border-stone-800"><td class="p-3 font-medium">{{ $product->name }}</td><td class="p-3">{{ $product->category?->name ?? '—' }}</td><td class="p-3 {{ $product->isLowStock() ? 'text-brand-600 font-bold' : '' }}">{{ $product->stock }}</td><td class="p-3">{{ (int) ($stat->qty ?? 0) }}</td><td class="p-3 text-right">{{ $currency }} {{ number_format($stat->revenue ?? 0, 2) }}</td></tr>
      @endforeach
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $products->links() }}</div>
@endsection
