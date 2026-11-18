<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('method');
            $table->string('checkout_request_id')->nullable()->index()->after('transaction_id');
            $table->string('merchant_request_id')->nullable()->after('checkout_request_id');
            $table->string('receipt_number')->nullable()->index()->after('merchant_request_id');
            $table->integer('result_code')->nullable()->after('receipt_number');
            $table->string('result_desc')->nullable()->after('result_code');
            $table->longText('callback_payload')->nullable()->after('raw_response');
            $table->timestamp('callback_received_at')->nullable()->after('callback_payload');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['phone', 'checkout_request_id', 'merchant_request_id', 'receipt_number', 'result_code', 'result_desc', 'callback_payload', 'callback_received_at']);
        });
    }
};