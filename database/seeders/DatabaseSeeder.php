<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            KategoriSeeder::class,
            SupplierSeeder::class,
            ObatSeeder::class,
            PenjualanSeeder::class,
        ]);
    }
}
