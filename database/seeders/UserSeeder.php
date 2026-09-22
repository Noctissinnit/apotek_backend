<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Akun contoh: 1 admin + 2 kasir.
     *
     * Password awal diambil dari env SEED_ADMIN_PASSWORD / SEED_KASIR_PASSWORD,
     * dengan fallback untuk development. firstOrCreate dipakai supaya menjalankan
     * seeder ulang tidak mereset password yang sudah diganti pengguna.
     */
    public function run(): void
    {
        $passwordAdmin = env('SEED_ADMIN_PASSWORD') ?: 'Admin12345';
        $passwordKasir = env('SEED_KASIR_PASSWORD') ?: 'Kasir12345';

        $akun = [
            ['name' => 'Admin Apotek', 'email' => 'admin@apotek.test', 'role' => User::ROLE_ADMIN, 'password' => $passwordAdmin],
            ['name' => 'Kasir Pagi', 'email' => 'kasir1@apotek.test', 'role' => User::ROLE_KASIR, 'password' => $passwordKasir],
            ['name' => 'Kasir Sore', 'email' => 'kasir2@apotek.test', 'role' => User::ROLE_KASIR, 'password' => $passwordKasir],
        ];

        foreach ($akun as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'role' => $data['role'],
                    'password' => $data['password'],
                    'aktif' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
