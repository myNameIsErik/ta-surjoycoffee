<?php

namespace Database\Seeders;

use App\Models\Journal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@cafe.test'],
            [
                'name' => 'Admin Cafe',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir@cafe.test'],
            [
                'name' => 'Kasir Demo',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'is_active' => true,
            ]
        );

        $this->call([
            AccountSeeder::class,
            CategorySeeder::class,
        ]);

        // Demo jurnal hanya dijalankan kalau DB benar-benar kosong (first deploy / local fresh).
        // Mencegah duplikat tiap container restart di production.
        if (Journal::count() === 0) {
            $this->call(JournalDemoSeeder::class);
        }
    }
}
