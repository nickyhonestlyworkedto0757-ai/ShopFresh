<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_best_seller')->default(false)->after('is_active');
            $table->boolean('is_sponsored')->default(false)->after('is_best_seller');
            $table->boolean('is_official_store')->default(false)->after('is_sponsored');
            $table->timestamp('flash_sale_ends_at')->nullable()->after('is_official_store');
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn(['is_best_seller', 'is_sponsored', 'is_official_store', 'flash_sale_ends_at']));
    }
};