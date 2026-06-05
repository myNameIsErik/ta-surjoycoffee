<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom category_id (nullable dulu) — hanya kalau belum ada
        if (! Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('category_id')->nullable()->after('name')->constrained('categories')->nullOnDelete();
            });
        }

        // 2. Backfill: hanya kalau kolom string 'category' masih ada (deployment baru tidak punya)
        if (Schema::hasColumn('products', 'category')) {
            $existing = DB::table('products')
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->pluck('category');

            foreach ($existing as $name) {
                DB::table('categories')->insertOrIgnore([
                    'name' => $name,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Map products.category_id berdasarkan nama (cross-database friendly via row-by-row)
            $categoryMap = DB::table('categories')->pluck('id', 'name');

            DB::table('products')
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->orderBy('id')
                ->chunkById(100, function ($rows) use ($categoryMap) {
                    foreach ($rows as $row) {
                        if (isset($categoryMap[$row->category])) {
                            DB::table('products')
                                ->where('id', $row->id)
                                ->update(['category_id' => $categoryMap[$row->category]]);
                        }
                    }
                });

            // Drop kolom category string yang lama
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('products', 'category')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('category')->nullable()->after('name');
            });

            // Restore string dari kategori (cross-DB friendly)
            $catNames = DB::table('categories')->pluck('name', 'id');
            DB::table('products')
                ->whereNotNull('category_id')
                ->orderBy('id')
                ->chunkById(100, function ($rows) use ($catNames) {
                    foreach ($rows as $row) {
                        if (isset($catNames[$row->category_id])) {
                            DB::table('products')
                                ->where('id', $row->id)
                                ->update(['category' => $catNames[$row->category_id]]);
                        }
                    }
                });
        }

        if (Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }
    }
};
