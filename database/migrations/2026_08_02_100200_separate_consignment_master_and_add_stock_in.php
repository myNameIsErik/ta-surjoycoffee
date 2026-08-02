<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Kolom FK baru ke master konsinyasi (nullable dulu selama migrasi data).
        Schema::table('consignments', function (Blueprint $table) {
            if (! Schema::hasColumn('consignments', 'consignment_product_id')) {
                $table->foreignId('consignment_product_id')->nullable()->after('consignee_id')
                    ->constrained('consignment_products')->restrictOnDelete();
            }
        });

        // 2. Migrasi data lama: cerminkan produk yang pernah dipakai konsinyasi ke master konsinyasi.
        if (Schema::hasColumn('consignments', 'product_id')) {
            $productIds = DB::table('consignments')->distinct()->pluck('product_id')->filter();
            foreach ($productIds as $pid) {
                $p = DB::table('products')->where('id', $pid)->first();
                if (! $p) {
                    continue;
                }
                $cpId = DB::table('consignment_products')->where('code', $p->code)->value('id');
                if (! $cpId) {
                    $cpId = DB::table('consignment_products')->insertGetId([
                        'code' => $p->code,
                        'name' => $p->name,
                        'unit' => $p->unit,
                        'sale_price' => $p->sale_price ?? 0,
                        'stock' => 0, // stok gudang konsinyasi diisi ulang lewat "Stok Masuk".
                        'min_stock' => 0,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                DB::table('consignments')->where('product_id', $pid)->update(['consignment_product_id' => $cpId]);
            }
        }

        // 3. Buang FK & kolom product_id lama.
        if (Schema::hasColumn('consignments', 'product_id')) {
            Schema::table('consignments', function (Blueprint $table) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            });
        }

        // 4. consignee_id boleh NULL (transaksi "Stok Masuk" tidak punya penerima).
        DB::statement('ALTER TABLE consignments MODIFY consignee_id BIGINT UNSIGNED NULL');

        // 5. Tambah tipe "stock_in" ke enum.
        DB::statement("ALTER TABLE consignments MODIFY type ENUM('stock_in','send','sold') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE consignments MODIFY type ENUM('send','sold') NOT NULL");
        DB::statement('ALTER TABLE consignments MODIFY consignee_id BIGINT UNSIGNED NOT NULL');

        Schema::table('consignments', function (Blueprint $table) {
            if (! Schema::hasColumn('consignments', 'product_id')) {
                $table->foreignId('product_id')->nullable()->after('consignee_id')
                    ->constrained('products')->restrictOnDelete();
            }
        });

        Schema::table('consignments', function (Blueprint $table) {
            if (Schema::hasColumn('consignments', 'consignment_product_id')) {
                $table->dropForeign(['consignment_product_id']);
                $table->dropColumn('consignment_product_id');
            }
        });
    }
};
