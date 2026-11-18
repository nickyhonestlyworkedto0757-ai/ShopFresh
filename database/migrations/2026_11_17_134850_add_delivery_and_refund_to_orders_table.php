<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('discount');
            $table->foreignId('delivery_zone_id')->nullable()->after('delivery_fee')->constrained('delivery_zones')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->after('delivery_zone_id')->constrained('users')->nullOnDelete();
            $table->string('delivery_status')->default('pending')->after('status');
            $table->decimal('refund_amount', 10, 2)->default(0)->after('total');
            $table->string('refund_status')->nullable()->after('refund_amount');
            $table->string('invoice_number')->nullable()->unique()->after('order_number');
        });
    }
    public function down(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_zone_id');
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropColumn(['delivery_fee', 'delivery_status', 'refund_amount', 'refund_status', 'invoice_number']);
        });
    }
};