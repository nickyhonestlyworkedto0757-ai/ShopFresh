<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');
        if ($q = trim((string) $request->input('q'))) {
            $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%")->orWhere('barcode', 'like', "%{$q}%"));
        }
        if ($category = $request->string('category')->toString()) {
            $query->where('category_id', $category);
        }
        if ($request->input('stock') === 'low') {
            $query->whereColumn('stock', '<=', 'low_stock_threshold');
        }
        $products = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();
        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.products.form', ['product' => new Product(), 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $product = Product::create($this->withImage($request, $data));
        $this->syncRelations($request, $product);
        if ($request->boolean('notify_customers')) {
            $count = \App\Support\Notifier::newProduct($product);
            ActivityLogger::log('product.notify', $product->name, "Notified {$count} customers");
        }
        ActivityLogger::log('product.create', $product->name, 'Product created');
        return redirect()->route('admin.products.index')->with('success', 'Product created.' . ($request->boolean('notify_customers') ? ' Subscribed customers were notified.' : ''));
    }

    public function edit(Product $product)
    {
        $product->load('images', 'variations');
        $categories = Category::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.products.form', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request);
        $product->update($this->withImage($request, $data, $product));
        $this->syncRelations($request, $product);
        ActivityLogger::log('product.update', $product->name, 'Product updated');
        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => ! $product->is_active]);
        ActivityLogger::log('product.toggle', $product->name, $product->is_active ? 'Enabled' : 'Disabled');
        return back()->with('success', $product->is_active ? 'Product enabled.' : 'Product disabled.');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        ActivityLogger::log('product.delete', $product->name, 'Product deleted');
        $product->delete();
        return back()->with('success', 'Product deleted.');
    }

    public function deleteImage(Product $product, ProductImage $image)
    {
        abort_if($image->product_id !== $product->id, 403);
        Storage::disk('public')->delete($image->path);
        $image->delete();
        return back()->with('success', 'Image removed.');
    }

    private function syncRelations(Request $request, Product $product): void
    {
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $i => $file) {
                $path = $file->store('products/gallery', 'public');
                $product->images()->create(['path' => $path, 'sort_order' => $product->images()->max('sort_order') + $i + 1]);
            }
        }

        $variations = $request->input('variations', []);
        $product->variations()->delete();
        foreach ($variations as $variation) {
            if (empty($variation['type']) || empty($variation['value'])) {
                continue;
            }
            $product->variations()->create([
                'type' => substr(trim($variation['type']), 0, 50),
                'value' => substr(trim($variation['value']), 0, 100),
                'price_delta' => (float) ($variation['price_delta'] ?? 0),
                'stock' => max(0, (int) ($variation['stock'] ?? 0)),
                'sku' => $variation['sku'] ?? null,
            ]);
        }
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . ($request->route('product')?->id ?? 'NULL'),
            'barcode' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'specifications' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'is_best_seller' => 'nullable|boolean',
            'is_sponsored' => 'nullable|boolean',
            'is_official_store' => 'nullable|boolean',
            'flash_sale_ends_at' => 'nullable|date',
        ]);
        unset($data['image'], $data['gallery'], $data['variations']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_best_seller'] = $request->boolean('is_best_seller');
        $data['is_sponsored'] = $request->boolean('is_sponsored');
        $data['is_official_store'] = $request->boolean('is_official_store');
        $data['flash_sale_ends_at'] = $data['flash_sale_ends_at'] ?: null;
        $data['low_stock_threshold'] = (int) ($data['low_stock_threshold'] ?? 5);
        if (($data['sale_price'] ?? null) === '') {
            $data['sale_price'] = null;
        }
        return $data;
    }

    private function withImage(Request $request, array $data, ?Product $product = null): array
    {
        if ($request->hasFile('image')) {
            if ($product?->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }
        return $data;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
