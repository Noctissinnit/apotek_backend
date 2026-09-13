<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama' => 'PT Kimia Farma Trading & Distribution',
                'telepon' => '021-4805436',
                'email' => 'cs@kftd.co.id',
                'alamat' => 'Jl. Budi Utomo No. 1, Jakarta Pusat',
                'nama_kontak' => 'Bpk. Hendra',
            ],
            [
                'nama' => 'PT Anugrah Pharmindo Lestari',
                'telepon' => '021-8378 8888',
                'email' => 'order@apl-pharma.co.id',
                'alamat' => 'Jl. Raya Bekasi KM 21, Jakarta Timur',
                'nama_kontak' => 'Ibu Ratna',
            ],
            [
                'nama' => 'PT Enseval Putera Megatrading',
                'telepon' => '021-4410222',
                'email' => 'sales@enseval.com',
                'alamat' => 'Jl. Pulo Lentut No. 10, Kawasan Industri Pulogadung',
                'nama_kontak' => 'Bpk. Yusuf',
            ],
            [
                'nama' => 'PT Bina San Prima',
                'telepon' => '022-6035432',
                'email' => 'info@binasanprima.co.id',
                'alamat' => 'Jl. Purnawarman No. 47, Bandung',
                'nama_kontak' => 'Ibu Sinta',
            ],
            [
                'nama' => 'PT Merapi Utama Pharma',
                'telepon' => '0274-563421',
                'email' => 'yogya@merapipharma.co.id',
                'alamat' => 'Jl. Magelang KM 6, Sleman, Yogyakarta',
                'nama_kontak' => 'Bpk. Bagas',
            ],
        ];

        foreach ($data as $item) {
            Supplier::updateOrCreate(['nama' => $item['nama']], $item + ['aktif' => true]);
        }
    }
}
