<?php

namespace Database\Seeders;

use App\Models\HomepageSection;
use Illuminate\Database\Seeder;

class HomepageSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['key' => 'recent', 'title' => 'Your Recent Finds', 'mode' => 'flag', 'value' => 'recent', 'sort_order' => 10],
            ['key' => 'flash', 'title' => 'Flash Sales | Live Now', 'subtitle' => 'Limited time offers', 'mode' => 'flag', 'value' => 'flash_sale', 'sort_order' => 20],
            ['key' => 'topselling', 'title' => 'Top Selling Items', 'mode' => 'flag', 'value' => 'best_seller', 'sort_order' => 30],
            ['key' => 'sponsored', 'title' => 'Sponsored Products', 'mode' => 'flag', 'value' => 'sponsored', 'sort_order' => 40],
            ['key' => 'blackfriday', 'title' => 'Black Friday | Top Deals', 'mode' => 'category', 'value' => 'black-friday', 'sort_order' => 50],
            ['key' => 'tvaudio', 'title' => 'TV & Audio Deals', 'mode' => 'category', 'value' => 'tv-audio', 'sort_order' => 60],
            ['key' => 'homedeals', 'title' => 'Home Deals', 'mode' => 'category', 'value' => 'home', 'sort_order' => 70],
            ['key' => 'appliances', 'title' => 'Appliances Deals', 'mode' => 'category', 'value' => 'appliances', 'sort_order' => 80],
            ['key' => 'beauty', 'title' => 'Beauty Essentials', 'mode' => 'category', 'value' => 'beauty', 'sort_order' => 90],
            ['key' => 'phoneaccessories', 'title' => 'Phones Accessories', 'mode' => 'category', 'value' => 'phones-accessories', 'sort_order' => 100],
            ['key' => 'promo20', 'title' => 'Get Extra 20% Off At Checkout', 'mode' => 'promo', 'value' => 'extra20', 'sort_order' => 110],
            ['key' => 'add4cart', 'title' => 'Add 4 Items To Cart', 'mode' => 'promo', 'value' => 'cart4', 'sort_order' => 120],
            ['key' => 'categories', 'title' => 'Browse Top Categories', 'mode' => 'categories', 'value' => null, 'sort_order' => 130],
            ['key' => 'chinatown', 'title' => 'China Town Deals', 'mode' => 'category', 'value' => 'china-town', 'sort_order' => 140],
            ['key' => 'mensfashion', 'title' => "Men's Fashion", 'mode' => 'category', 'value' => 'mens-fashion', 'sort_order' => 150],
            ['key' => 'workessentials', 'title' => 'Work Essentials', 'mode' => 'category', 'value' => 'work-essentials', 'sort_order' => 160],
            ['key' => 'officialstores', 'title' => 'Official Stores', 'mode' => 'flag', 'value' => 'official_store', 'sort_order' => 170],
            ['key' => 'xiaomi', 'title' => 'Xiaomi | Official Store Deals', 'mode' => 'category', 'value' => 'xiaomi-official', 'sort_order' => 180],
            ['key' => 'kidsandbaby', 'title' => 'Kids & Baby Deals', 'mode' => 'category', 'value' => 'kids-baby', 'sort_order' => 190],
            ['key' => 'newarrivals', 'title' => 'New Arrivals | More Deals To Browse', 'mode' => 'flag', 'value' => 'recent', 'sort_order' => 200],
        ];

        foreach ($sections as $s) {
            HomepageSection::updateOrCreate(['key' => $s['key']], $s + ['is_active' => true, 'max_items' => 12]);
        }
    }
}