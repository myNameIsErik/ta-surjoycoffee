<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop enum constraint dulu jadi VARCHAR sementara (cross-DB friendly)
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('staff_gudang')->change();
        });

        // Map existing 'kasir' jadi 'staff_gudang'
        DB::table('users')->where('role', 'kasir')->update(['role' => 'staff_gudang']);
    }

    public function down(): void
    {
        // Map balik 'staff_gudang' jadi 'kasir'
        DB::table('users')->where('role', 'staff_gudang')->update(['role' => 'kasir']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'kasir'])->default('kasir')->change();
        });
    }
};
