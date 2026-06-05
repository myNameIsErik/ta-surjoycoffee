<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom category_id (nullable dulu)
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('name')->constrained('categories')->nullOnDelete();
        });

        // 2. Backfill: ambil distinct category string yang sudah ada, buat row di categories
        $existing = DB::table('products')->whereNotNull('category')->where('category', '!=', '')->distinct()->pluck('category');
        foreach ($existing as $name) {
            DB::table('categories')->insertOrIgnore([
                'name' => $name,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Map products.category_id berdasarkan nama
        DB::statement("
            UPDATE products p
            INNER JOIN categories c ON c.name = p.category
            SET p.category_id = c.id
            WHERE p.category IS NOT NULL AND p.category <> ''
        ");

        // 4. Drop kolom category string yang lama
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name');
        });

        DB::statement("
            UPDATE products p
            LEFT JOIN categories c ON c.id = p.category_id
            SET p.category = c.name
        ");

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
