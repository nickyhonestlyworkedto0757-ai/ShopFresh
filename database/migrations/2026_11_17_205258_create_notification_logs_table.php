<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel'); // email, sms
            $table->string('type')->default('custom'); // welcome, order_placed, order_shipped, ...
            $table->string('recipient'); // email or phone
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('unique_key')->nullable()->unique(); // dedupe
            $table->string('status')->default('sent'); // sent, failed, skipped
            $table->unsignedTinyInteger('attempts')->default(1);
            $table->decimal('cost', 8, 2)->default(0);
            $table->text('error')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('notification_logs'); }
};