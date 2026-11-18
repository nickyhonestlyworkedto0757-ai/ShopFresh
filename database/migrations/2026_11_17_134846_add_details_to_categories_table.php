<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
            $table->string('description')->nullable()->after('slug');
            $table->string('icon')->nullable()->after('description');
            $table->string('image')->nullable()->after('icon');
            $table->unsignedInteger('sort_order')->default(0)->after('image');
        });
    }
    public function down(): void {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn(['description', 'icon', 'image', 'sort_order']);
        });
    }
};