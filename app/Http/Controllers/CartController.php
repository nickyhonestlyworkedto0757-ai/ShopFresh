<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        return view('shop.cart', Cart::detailed());
    }

    public function add(Request $request, Product $product)
    {
        $qty = max(1, (int) $request->input('qty', 1));
        Cart::add($product->id, min($qty, max(1, $product->stock)));
        return back()->with('success', $product->name . ' added to cart.');
    }

    public function update(Request $request, Product $product)
    {
        Cart::update($product->id, (int) $request->input('qty', 1));
        return back();
    }

    public function remove(Product $product)
    {
        Cart::remove($product->id);
        return back()->with('success', 'Item removed.');
    }
}
