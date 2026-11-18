<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Database\Seeders\HomepageSectionSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\User::updateOrCreate(['email' => 'admin@shopfresh.test'], [
            'name' => 'ShopFresh Admin',
            'password' => 'password',
            'is_admin' => true,
            'role' => 'super_admin',
        ]);

        collect([
            'Fruits', 'Vegetables', 'Dairy & Eggs', 'Bakery', 'Pantry', 'Beverages',
        ])->each(fn ($name) => Category::updateOrCreate(['slug' => Str::slug($name)], ['name' => $name]));

        collect([
            ['key' => 'new_product_alert', 'name' => 'New product alert', 'channel' => 'both', 'subject' => 'Fresh just landed!', 'body' => "Hi {{name}},\n\nNew on the shelves: {{product_name}}\nPrice: {{total}}\n\nShop now: {{link}}"],
            ['key' => 'flash_sale', 'name' => 'Flash sale', 'channel' => 'both', 'subject' => 'Flash sale — up to 30% off', 'body' => "Hi {{name}},\n\nDon't miss our flash sale! Visit {{link}} and grab great deals before they're gone."],
            ['key' => 'order_status', 'name' => 'Order status update', 'channel' => 'both', 'subject' => 'Update on your order', 'body' => "Hi {{name}},\n\nStatus of order {{order_number}}: updated. Check {{link}} for details."],
            ['key' => 'back_in_stock', 'name' => 'Back in stock', 'channel' => 'both', 'subject' => 'Back in stock!', 'body' => "Hi {{name}},\n\n{{product_name}} is back in stock. {{link}}"],
        ])->each(fn ($t) => \App\Models\NotificationTemplate::updateOrCreate(['key' => $t['key']], ['name' => $t['name'], 'channel' => $t['channel'], 'subject' => $t['subject'], 'body' => $t['body'], 'is_active' => true]));
            $this->call(HomepageSectionSeeder::class);
    }
}
