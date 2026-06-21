<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['Sembako',              'Beras, gula, minyak, tepung, dan bahan pokok lainnya'],
            ['Minuman Kemasan',      'Air mineral, teh kotak, susu UHT, soft drink, dll'],
            ['Snack & Makanan Ringan', 'Biskuit, kerupuk, kacang, permen, dll'],
            ['Mie & Bumbu Dapur',    'Mie instan, kecap, saus, bumbu masak'],
            ['Personal Care',        'Sabun, shampoo, pasta gigi, deterjen'],
            ['Rokok',                'Produk rokok & tembakau'],
            ['Lain-lain',            'Kategori umum untuk produk yang belum tergolong'],
        ];

        foreach ($defaults as [$name, $desc]) {
            Category::updateOrCreate(
                ['name' => $name],
                ['description' => $desc, 'is_active' => true]
            );
        }

        // Hapus kategori lama khusus cafe
        Category::whereIn('name', ['Minuman', 'Makanan', 'Snack', 'Bahan Baku', 'Perlengkapan', 'Dessert'])->delete();
    }
}
