<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'Analgesik & Antipiretik', 'deskripsi' => 'Obat pereda nyeri dan penurun demam.'],
            ['nama' => 'Antibiotik', 'deskripsi' => 'Obat keras untuk infeksi bakteri, wajib dengan resep dokter.'],
            ['nama' => 'Vitamin & Suplemen', 'deskripsi' => 'Multivitamin, mineral, dan suplemen daya tahan tubuh.'],
            ['nama' => 'Obat Batuk & Flu', 'deskripsi' => 'Sirup dan tablet untuk batuk, pilek, serta gejala flu.'],
            ['nama' => 'Obat Pencernaan', 'deskripsi' => 'Obat maag, diare, sembelit, dan gangguan lambung.'],
            ['nama' => 'Antihistamin & Alergi', 'deskripsi' => 'Obat untuk reaksi alergi, gatal, dan biduran.'],
            ['nama' => 'Obat Luar & Topikal', 'deskripsi' => 'Salep, krim, antiseptik, dan obat pemakaian luar.'],
            ['nama' => 'Kardiovaskular', 'deskripsi' => 'Obat hipertensi, kolesterol, dan jantung.'],
            ['nama' => 'Antidiabetes', 'deskripsi' => 'Obat penurun gula darah untuk penderita diabetes.'],
            ['nama' => 'Obat Herbal', 'deskripsi' => 'Jamu dan obat tradisional terstandar.'],
            ['nama' => 'Alat Kesehatan', 'deskripsi' => 'Masker, perban, termometer, dan perlengkapan medis.'],
        ];

        foreach ($data as $item) {
            Kategori::updateOrCreate(
                ['nama' => $item['nama']],
                ['slug' => Str::slug($item['nama']), 'deskripsi' => $item['deskripsi']]
            );
        }
    }
}
