<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('customer')->after('is_admin');
            $table->boolean('is_blocked')->default(false)->after('role');
        });
        DB::table('users')->where('is_admin', true)->update(['role' => 'super_admin']);
    }
    public function down(): void {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'is_blocked']));
    }
};