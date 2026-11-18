<?php

namespace App\Support;

use App\Models\Product;

class Cart
{
    public static function items(): array
    {
        return session('cart', []);
    }

    public static function add(int $productId, int $qty = 1): void
    {
        $cart = self::items();
        $cart[$productId] = ($cart[$productId] ?? 0) + $qty;
        session(['cart' => $cart]);
    }

    public static function update(int $productId, int $qty): void
    {
        $cart = self::items();
        if ($qty <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $qty;
        }
        session(['cart' => $cart]);
    }

    public static function remove(int $productId): void
    {
        $cart = self::items();
        unset($cart[$productId]);
        session(['cart' => $cart]);
    }

    public static function clear(): void
    {
        session()->forget('cart');
        session()->forget('coupon_code');
    }

    public static function detailed(): array
    {
        $cart = self::items();
        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');
        $items = [];
        $subtotal = 0;
        foreach ($cart as $id => $qty) {
            $product = $products->get($id);
            if (! $product) continue;
            $price = $product->effectivePrice();
            $line = $price * $qty;
            $subtotal += $line;
            $items[] = ['product' => $product, 'qty' => $qty, 'price' => $price, 'line' => $line];
        }
        return ['items' => $items, 'subtotal' => $subtotal, 'count' => array_sum($cart)];
    }
}
