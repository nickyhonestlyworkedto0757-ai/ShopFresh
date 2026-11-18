<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('email_order_updates')->default(true);
            $table->boolean('sms_order_updates')->default(true);
            $table->boolean('email_new_products')->default(true);
            $table->boolean('sms_new_products')->default(0);
            $table->boolean('email_promotions')->default(true);
            $table->boolean('sms_promotions')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('notification_preferences'); }
};