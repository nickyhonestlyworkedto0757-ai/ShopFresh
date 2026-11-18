<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function home()
    {
        $featured = Product::active()->with('category')->where('is_featured', true)->latest()->take(8)->get();
        $latest = Product::active()->with('category')->latest()->take(8)->get();
        $categories = Category::withCount('products')->get();
        $banners = Banner::where('is_active', true)->orderBy('sort_order')->orderBy('id', 'desc')->get();

        $sections = \App\Models\HomepageSection::where('is_active', true)->orderBy('sort_order')->get()->map(function ($section) {
            if (in_array($section->mode, ['categories', 'promo'])) {
                return ['section' => $section];
            }
            $products = $section->products();
            return $products->isNotEmpty() ? ['section' => $section, 'products' => $products] : null;
        })->filter()->values();

        $flashEndsAt = Product::whereNotNull('flash_sale_ends_at')->where('flash_sale_ends_at', '>', now())->orderBy('flash_sale_ends_at')->value('flash_sale_ends_at');

        return view('shop.home', compact('featured', 'latest', 'categories', 'banners', 'sections', 'flashEndsAt'));
    }

    public function catalog(Request $request)
    {
        $query = Product::with('category')->active()->withCount('reviews');
        if ($search = $request->string('q')->toString()) {
            $query->where(fn ($w) => $w->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"));
        }
        if ($category = $request->string('category')->toString()) {
            $query->whereHas('category', fn ($w) => $w->where('slug', $category));
        }
        if ($request->string('sort')->toString() === 'price_asc') $query->orderByRaw('COALESCE(sale_price, price) asc');
        elseif ($request->string('sort')->toString() === 'price_desc') $query->orderByRaw('COALESCE(sale_price, price) desc');
        else $query->latest();

        if ($flag = $request->string('flag')->toString()) {
            match ($flag) {
                'featured' => $query->where('is_featured', true),
                'best_seller' => $query->where('is_best_seller', true),
                'sponsored' => $query->where('is_sponsored', true),
                'official_store' => $query->where('is_official_store', true),
                'flash_sale' => $query->whereNotNull('flash_sale_ends_at')->where('flash_sale_ends_at', '>', now()),
                default => null,
            };
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::all();
        return view('shop.catalog', compact('products', 'categories', 'search'));
    }

    public function product(string $slug)
    {
        $product = Product::with(['category', 'reviews.user', 'images', 'variations'])->active()->where('slug', $slug)->firstOrFail();
        $related = Product::active()->where('category_id', $product->category_id)->where('id', '!=', $product->id)->take(4)->get();
        return view('shop.product', compact('product', 'related'));
    }

    public function review(Request $request, Product $product)
    {
        $validated = $request->validate(['rating' => 'required|integer|min:1|max:5', 'comment' => 'nullable|string|max:1000']);
        Review::updateOrCreate(
            ['product_id' => $product->id, 'user_id' => $request->user()->id],
            $validated
        );
        return back()->with('success', 'Thanks for your review!');
    }
}
