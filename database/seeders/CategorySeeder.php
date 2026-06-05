<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['Minuman',     'Kopi, teh, jus, dan minuman lainnya'],
            ['Makanan',     'Menu makanan utama'],
            ['Snack',       'Makanan ringan, kue, dessert'],
            ['Bahan Baku',  'Bahan baku produksi (kopi, susu, gula, dll)'],
            ['Perlengkapan','Cup, sedotan, tissue, dll'],
        ];

        foreach ($defaults as [$name, $desc]) {
            Category::updateOrCreate(
                ['name' => $name],
                ['description' => $desc, 'is_active' => true]
            );
        }
    }
}
