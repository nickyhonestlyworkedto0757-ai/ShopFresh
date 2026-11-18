@extends('layouts.app')
@section('title', 'Shop — ShopFresh')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 grid md:grid-cols-4 gap-6">
  <aside class="md:col-span-1">
    <div class="bg-white rounded-2xl border border-stone-100 p-4">
      <h3 class="font-bold mb-2 flex items-center gap-2"><i data-lucide="list" class="w-5 h-5"></i>Categories</h3>
      <a href="{{ route('catalog') }}" class="block px-3 py-2 rounded-lg text-sm {{ !request('category') ? 'bg-leaf-50 text-leaf-700 font-semibold' : 'hover:bg-stone-50' }}">All products</a>
      @foreach($categories as $cat)
        <a href="{{ route('catalog', array_merge(request()->query(), ['category' => $cat->slug])) }}" class="block px-3 py-2 rounded-lg text-sm {{ request('category') === $cat->slug ? 'bg-leaf-50 text-leaf-700 font-semibold' : 'hover:bg-stone-50' }}">{{ $cat->name }}</a>
      @endforeach
      <h3 class="font-bold mt-4 mb-2 flex items-center gap-2"><i data-lucide="arrow-up-down" class="w-5 h-5"></i>Sort</h3>
      @foreach(['' => 'Newest', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low'] as $val => $label)
        <a href="{{ route('catalog', array_merge(request()->query(), ['sort' => $val])) }}" class="block px-3 py-2 rounded-lg text-sm {{ request('sort', '') === $val ? 'bg-sun-100 font-semibold' : 'hover:bg-stone-50' }}">{{ $label }}</a>
      @endforeach
    </div>
  </aside>
  <div class="md:col-span-3">
    @if(request('q'))<p class="text-sm text-stone-500 mb-3">Results for "<b>{{ request('q') }}</b>" — {{ $products->total() }} found</p>@endif
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
      @forelse($products as $product)
        @include('shop._card', ['product' => $product])
      @empty
        <div class="col-span-full text-center py-16 text-stone-400">
          <i data-lucide="search-x" class="w-12 h-12 mx-auto mb-3"></i>
          <p>No products found.</p>
        </div>
      @endforelse
    </div>
    <div class="mt-6">{{ $products->links() }}</div>
  </div>
</div>
@endsection
