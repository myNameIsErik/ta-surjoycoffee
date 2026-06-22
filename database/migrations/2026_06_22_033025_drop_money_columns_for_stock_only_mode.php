<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'cost_price')) {
                $table->dropColumn('cost_price');
            }
            if (Schema::hasColumn('products', 'sale_price')) {
                $table->dropColumn('sale_price');
            }
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            foreach (['unit_cost', 'unit_price', 'total_cost', 'total_price'] as $col) {
                if (Schema::hasColumn('stock_movements', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('consignments', function (Blueprint $table) {
            foreach (['unit_cost', 'unit_price', 'total_cost', 'total_price'] as $col) {
                if (Schema::hasColumn('consignments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 18, 2)->default(0)->after('unit');
            $table->decimal('sale_price', 18, 2)->default(0)->after('cost_price');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('unit_cost', 18, 2)->default(0)->after('quantity');
            $table->decimal('unit_price', 18, 2)->default(0)->after('unit_cost');
            $table->decimal('total_cost', 18, 2)->default(0)->after('unit_price');
            $table->decimal('total_price', 18, 2)->default(0)->after('total_cost');
        });

        Schema::table('consignments', function (Blueprint $table) {
            $table->decimal('unit_cost', 18, 2)->default(0)->after('quantity');
            $table->decimal('unit_price', 18, 2)->default(0)->after('unit_cost');
            $table->decimal('total_cost', 18, 2)->default(0)->after('unit_price');
            $table->decimal('total_price', 18, 2)->default(0)->after('total_cost');
        });
    }
};
