<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('coupons', fn (Blueprint $t) => $t->decimal('min_purchase', 10, 2)->default(0));
    }
    public function down(): void {
        Schema::table('coupons', fn (Blueprint $t) => $t->dropColumn('min_purchase'));
    }
};