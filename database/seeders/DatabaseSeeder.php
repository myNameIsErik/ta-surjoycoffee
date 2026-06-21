<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@starjaya.test'],
            [
                'name' => 'Admin STAR JAYA',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'gudang@starjaya.test'],
            [
                'name' => 'Staff Gudang Demo',
                'password' => Hash::make('password'),
                'role' => 'staff_gudang',
                'is_active' => true,
            ]
        );

        // Bersihin user lama (kalau ada sisa dari project cafe lama)
        User::whereIn('email', ['admin@cafe.test', 'kasir@cafe.test'])->delete();

        $this->call([
            AccountSeeder::class,
            CategorySeeder::class,
        ]);
    }
}
