<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Obat;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ObatSeeder extends Seeder
{
    /**
     * Contoh master data obat apotek.
     *
     * Kolom "kadaluarsa_bulan" = jumlah bulan dari hari ini, supaya data contoh
     * tidak ikut basi kalau seeder dijalankan berbulan-bulan kemudian.
     * Beberapa baris sengaja dibuat stok tipis / hampir kadaluarsa agar filter
     * ?stok_menipis=1 dan ?akan_kadaluarsa=90 langsung ada isinya.
     */
    public function run(): void
    {
        $kategori = Kategori::pluck('id', 'slug');
        $supplier = Supplier::orderBy('id')->pluck('id')->all();

        foreach ($this->daftarObat() as $row) {
            $kategoriId = $kategori[$row['kategori']] ?? null;

            if (! $kategoriId) {
                continue;
            }

            Obat::updateOrCreate(
                ['kode_obat' => $row['kode_obat']],
                [
                    'nama' => $row['nama'],
                    'kategori_id' => $kategoriId,
                    'supplier_id' => $supplier[$row['supplier']] ?? null,
                    'golongan' => $row['golongan'],
                    'bentuk_sediaan' => $row['bentuk_sediaan'],
                    'satuan' => $row['satuan'],
                    'kandungan' => $row['kandungan'],
                    'produsen' => $row['produsen'],
                    'harga_beli' => $row['harga_beli'],
                    'harga_jual' => $row['harga_jual'],
                    'stok' => $row['stok'],
                    'stok_minimum' => $row['stok_minimum'],
                    'tanggal_kadaluarsa' => now()->addMonths($row['kadaluarsa_bulan'])->toDateString(),
                    'deskripsi' => $row['deskripsi'],
                    'aktif' => true,
                ]
            );
        }
    }

    private function daftarObat(): array
    {
        return [
            // ---------------------------------------- Analgesik & Antipiretik
            [
                'kode_obat' => 'OBT-0001', 'nama' => 'Paracetamol 500 mg',
                'kategori' => 'analgesik-antipiretik', 'supplier' => 0, 'golongan' => 'bebas',
                'bentuk_sediaan' => 'Tablet', 'satuan' => 'strip', 'kandungan' => 'Paracetamol 500 mg',
                'produsen' => 'Kimia Farma', 'harga_beli' => 2800, 'harga_jual' => 4500,
                'stok' => 350, 'stok_minimum' => 50, 'kadaluarsa_bulan' => 26,
                'deskripsi' => 'Meredakan demam dan nyeri ringan sampai sedang. Isi 10 tablet per strip.',
            ],
            [
                'kode_obat' => 'OBT-0002', 'nama' => 'Panadol Extra',
                'kategori' => 'analgesik-antipiretik', 'supplier' => 2, 'golongan' => 'bebas',
                'bentuk_sediaan' => 'Kaplet', 'satuan' => 'strip', 'kandungan' => 'Paracetamol 500 mg, Kafein 65 mg',
                'produsen' => 'Sterling Products', 'harga_beli' => 9500, 'harga_jual' => 13500,
                'stok' => 120, 'stok_minimum' => 24, 'kadaluarsa_bulan' => 22,
                'deskripsi' => 'Pereda nyeri kepala dengan tambahan kafein.',
            ],
            [
                'kode_obat' => 'OBT-0003', 'nama' => 'Bodrex Migra',
                'kategori' => 'analgesik-antipiretik', 'supplier' => 3, 'golongan' => 'bebas',
                'bentuk_sediaan' => 'Tablet', 'satuan' => 'strip', 'kandungan' => 'Paracetamol 350 mg, Propifenazon 150 mg, Kafein 50 mg',
                'produsen' => 'Tempo Scan Pacific', 'harga_beli' => 5200, 'harga_jual' => 7500,
                'stok' => 95, 'stok_minimum' => 20, 'kadaluarsa_bulan' => 19,
                'deskripsi' => 'Untuk sakit kepala sebelah (migrain).',
            ],
            [
                'kode_obat' => 'OBT-0004', 'nama' => 'Ibuprofen 400 mg',
                'kategori' => 'analgesik-antipiretik', 'supplier' => 0, 'golongan' => 'bebas_terbatas',
                'bentuk_sediaan' => 'Tablet salut selaput', 'satuan' => 'strip', 'kandungan' => 'Ibuprofen 400 mg',
                'produsen' => 'Hexpharm Jaya', 'harga_beli' => 4200, 'harga_jual' => 6500,
                'stok' => 180, 'stok_minimum' => 40, 'kadaluarsa_bulan' => 24,
                'deskripsi' => 'Antiinflamasi non-steroid untuk nyeri dan radang. Diminum setelah makan.',
            ],
            [
                'kode_obat' => 'OBT-0005', 'nama' => 'Asam Mefenamat 500 mg',
                'kategori' => 'analgesik-antipiretik', 'supplier' => 1, 'golongan' => 'keras',
                'bentuk_sediaan' => 'Kaplet', 'satuan' => 'strip', 'kandungan' => 'Asam Mefenamat 500 mg',
                'produsen' => 'Dexa Medica', 'harga_beli' => 6800, 'harga_jual' => 9500,
                'stok' => 140, 'stok_minimum' => 30, 'kadaluarsa_bulan' => 20,
                'deskripsi' => 'Nyeri gigi dan nyeri haid. Harus dengan resep dokter.',
            ],
            [
                'kode_obat' => 'OBT-0006', 'nama' => 'Natrium Diklofenak 50 mg',
                'kategori' => 'analgesik-antipiretik', 'supplier' => 1, 'golongan' => 'keras',
                'bentuk_sediaan' => 'Tablet salut enterik', 'satuan' => 'strip', 'kandungan' => 'Natrium Diklofenak 50 mg',
                'produsen' => 'Novell Pharmaceutical', 'harga_beli' => 8500, 'harga_jual' => 12000,
                'stok' => 60, 'stok_minimum' => 20, 'kadaluarsa_bulan' => 15,
                'deskripsi' => 'Antinyeri dan antiradang untuk keluhan sendi.',
            ],

            // ------------------------------------------------------ Antibiotik
            [
                'kode_obat' => 'OBT-0101', 'nama' => 'Amoxicillin 500 mg',
                'kategori' => 'antibiotik', 'supplier' => 0, 'golongan' => 'keras',
                'bentuk_sediaan' => 'Kapsul', 'satuan' => 'strip', 'kandungan' => 'Amoxicillin trihydrate 500 mg',
                'produsen' => 'Kimia Farma', 'harga_beli' => 9200, 'harga_jual' => 13000,
                'stok' => 210, 'stok_minimum' => 40, 'kadaluarsa_bulan' => 18,
                'deskripsi' => 'Antibiotik golongan penisilin. Wajib dihabiskan sesuai anjuran dokter.',
            ],
            [
                'kode_obat' => 'OBT-0102', 'nama' => 'Cefadroxil 500 mg',
                'kategori' => 'antibiotik', 'supplier' => 1, 'golongan' => 'keras',
                'bentuk_sediaan' => 'Kapsul', 'satuan' => 'strip', 'kandungan' => 'Cefadroxil monohydrate 500 mg',
                'produsen' => 'Sanbe Farma', 'harga_beli' => 18500, 'harga_jual' => 25000,
                'stok' => 85, 'stok_minimum' => 25, 'kadaluarsa_bulan' => 16,
                'deskripsi' => 'Antibiotik sefalosporin generasi pertama.',
            ],
            [
                'kode_obat' => 'OBT-0103', 'nama' => 'Ciprofloxacin 500 mg',
                'kategori' => 'antibiotik', 'supplier' => 2, 'golongan' => 'keras',
                'bentuk_sediaan' => 'Tablet salut selaput', 'satuan' => 'strip', 'kandungan' => 'Ciprofloxacin HCl 500 mg',
                'produsen' => 'Indofarma', 'harga_beli' => 14000, 'harga_jual' => 19500,
                'stok' => 18, 'stok_minimum' => 25, 'kadaluarsa_bulan' => 11,
                'deskripsi' => 'Antibiotik kuinolon untuk infeksi saluran kemih dan pencernaan.',
            ],
            [
                'kode_obat' => 'OBT-0104', 'nama' => 'Amoxicillin Sirup Kering 125 mg/5 ml',
                'kategori' => 'antibiotik', 'supplier' => 0, 'golongan' => 'keras',
                'bentuk_sediaan' => 'Sirup kering', 'satuan' => 'botol', 'kandungan' => 'Amoxicillin 125 mg per 5 ml, 60 ml',
                'produsen' => 'Hexpharm Jaya', 'harga_beli' => 12500, 'harga_jual' => 17000,
                'stok' => 45, 'stok_minimum' => 15, 'kadaluarsa_bulan' => 14,
                'deskripsi' => 'Antibiotik anak. Larutkan dengan air matang sebelum digunakan.',
            ],

            // -------------------------------------------- Vitamin & Suplemen
            [
                'kode_obat' => 'OBT-0201', 'nama' => 'Vitamin C IPI 50 mg',
                'kategori' => 'vitamin-suplemen', 'supplier' => 3, 'golongan' => 'bebas',
                'bentuk_sediaan' => 'Tablet', 'satuan' => 'botol', 'kandungan' => 'Asam askorbat 50 mg, isi 45 tablet',
                'produsen' => 'Supra Ferbindo Farma', 'harga_beli' => 5500, 'harga_jual' => 8000,
                'stok' => 240, 'stok_minimum' => 40, 'kadaluarsa_bulan' => 28,
                'deskripsi' => 'Suplemen vitamin C harian.',
            ],
            [
                'kode_obat' => 'OBT-0202', 'nama' => 'Redoxon Effervescent 1000 mg',
                'kategori' => 'vitamin-suplemen', 'supplier' => 2, 'golongan' => 'bebas',
                'bentuk_sediaan' => 'Tablet effervescent', 'satuan' => 'tube', 'kandungan' => 'Vitamin C 1000 mg, isi 10 tablet',
                'produsen' => 'Bayer Indonesia', 'harga_beli' => 32000, 'harga_jual' => 42000,
                'stok' => 60, 'stok_minimum' => 15, 'kadaluarsa_bulan' => 21,
                'deskripsi' => 'Vitamin C dosis tinggi, larut dalam air.',
            ],
            [
                'kode_obat' => 'OBT-0203', 'nama' => 'Enervon-C Multivitamin',
                'kategori' => 'vitamin-suplemen', 'supplier' => 2, 'golongan' => 'bebas',
                'bentuk_sediaan' => 'Tablet salut gula', 'satuan' => 'strip', 'kandungan' => 'Vitamin C 500 mg, Vitamin B kompleks',
                'produsen' => 'Darya-Varia', 'harga_beli' => 11000, 'harga_jual' => 15500,
                'stok' => 130, 'stok_minimum' => 30, 'kadaluarsa_bulan' => 25,
                'deskripsi' => 'Multivitamin untuk menjaga stamina.',
            ],
            [
                'kode_obat' => 'OBT-0204', 'nama' => 'Imboost Force Tablet',
                'kategori' => 'vitamin-suplemen', 'supplier' => 1, 'golongan' => 'bebas',
                'bentuk_sediaan' => 'Tablet', 'satuan' => 'strip', 'kandungan' => 'Echinacea purpurea 250 mg, Black elderberry 400 mg, Zinc 10 mg',
                'produsen' => 'Soho Industri Pharmasi', 'harga_beli' => 28000, 'harga_jual' => 37500,
                'stok' => 70, 'stok_minimum' => 20, 'kadaluarsa_bulan' => 17,
                'deskripsi' => 'Suplemen peningkat daya tahan tubuh.',
            ],
            [
                'kode_obat' => 'OBT-0205', 'nama' => 'Blackmores Vitamin D3 1000 IU',
                'kategori' => 'vitamin-suplemen', 'supplier' => 4, 'golongan' => 'bebas',
                'bentuk_sediaan' => 'Kapsul lunak', 'satuan' => 'botol', 'kandungan' => 'Cholecalciferol 1000 IU, isi 60 kapsul',
                'produsen' => 'Blackmores', 'harga_beli' => 96000, 'harga_jual' => 125000,
                'stok' => 25, 'stok_minimum' => 10, 'kadaluarsa_bulan' => 23,
                'deskripsi' => 'Suplemen vitamin D untuk kesehatan tulang.',
            ],

            // -------------------------------------------- Obat Batuk & Flu
            [
                'kode_obat' => 'OBT-0301', 'nama' => 'OBH Combi Batuk Flu 100 ml',
                'kategori' => 'obat-batuk-flu', 'supplier' => 3, 'golongan' => 'bebas_terbatas',
                'bentuk_sediaan' => 'Sirup', 'satuan' => 'botol', 'kandungan' => 'Paracetamol, Ephedrine HCl, Chlorpheniramine maleate',
                'produsen' => 'Combiphar', 'harga_beli' => 14500, 'harga_jual' => 19000,
                'stok' => 88, 'stok_minimum' => 20, 'kadaluarsa_bulan' => 20,
                'deskripsi' => 'Meredakan batuk berdahak disertai gejala flu.',
            ],
            [
                'kode_obat' => 'OBT-0302', 'nama' => 'Woods Peppermint Antitussive 60 ml',
                'kategori' => 'obat-batuk-flu', 'supplier' => 2, 'golongan' => 'bebas_terbatas',
                'bentuk_sediaan' => 'Sirup', 'satuan' => 'botol', 'kandungan' => 'Dextromethorphan HBr, Diphenhydramine HCl',
                'produsen' => 'Kalbe Farma', 'harga_beli' => 21000, 'harga_jual' => 27500,
                'stok' => 54, 'stok_minimum' => 15, 'kadaluarsa_bulan' => 18,
                'deskripsi' => 'Untuk batuk kering tidak berdahak.',
            ],
            [
                'kode_obat' => 'OBT-0303', 'nama' => 'Actifed Plus Expectorant 60 ml',
                'kategori' => 'obat-batuk-flu', 'supplier' => 2, 'golongan' => 'bebas_terbatas',
                'bentuk_sediaan' => 'Sirup', 'satuan' => 'botol', 'kandungan' => 'Triprolidine HCl, Pseudoephedrine HCl, Guaifenesin',
                'produsen' => 'Glaxo Wellcome', 'harga_beli' => 24000, 'harga_jual' => 31000,
                'stok' => 12, 'stok_minimum' => 15, 'kadaluarsa_bulan' => 9,
                'deskripsi' => 'Batuk berdahak disertai hidung tersumbat.',
            ],
            [
                'kode_obat' => 'OBT-0304', 'nama' => 'Ambroxol 30 mg',
                'kategori' => 'obat-batuk-flu', 'supplier' => 0, 'golongan' => 'keras',
                'bentuk_sediaan' => 'Tablet', 'satuan' => 'strip', 'kandungan' => 'Ambroxol HCl 30 mg',
                'produsen' => 'Dexa Medica', 'harga_beli' => 5800, 'harga_jual' => 8500,
                'stok' => 160, 'stok_minimum' => 30, 'kadaluarsa_bulan' => 22,
                'deskripsi' => 'Pengencer dahak (mukolitik).',
            ],

            // ----------------------------------------------- Obat Pencernaan
            [
                'kode_obat' => 'OBT-0401', 'nama' => 'Promag Tablet',
                'kategori' => 'obat-pencernaan', 'supplier' => 3, 'golongan' => 'bebas',
                'bentuk_sediaan' => 'Tablet kunyah', 'satuan' => 'strip', 'kandungan' => 'Hydrotalcite, Mg(OH)2, Simethicone',
                'produsen' => 'Kalbe Farma', 'harga_beli' => 8200, 'harga_jual' => 11500,
                'stok' => 200, 'stok_minimum' => 40, 'kadaluarsa_bulan' => 24,
                'deskripsi' => 'Meredakan gejala maag dan kembung.',
            ],
            [
                'kode_obat' => 'OBT-0402', 'nama' => 'Mylanta Cair 150 ml',
                'kategori' => 'obat-pencernaan', 'supplier' => 2, 'golongan' => 'bebas',
                'bentuk_sediaan' => 'Suspensi', 'satuan' => 'botol', 'kandungan' => 'Al(OH)3, Mg(OH)2, Simethicone',
                'produsen' => 'Pfizer Indonesia', 'harga_beli' => 26000, 'harga_jual' => 34000,
                'stok' => 40, 'stok_minimum' => 12, 'kadaluarsa_bulan' => 15,
                'deskripsi' => 'Antasida cair untuk nyeri lambung.',
            ],
            [
                'kode_obat' => 'OBT-0403', 'nama' => 'Omeprazole 20 mg',
                'kategori' => 'obat-pencernaan', 'supplier' => 1, 'golongan' => 'keras',
                'bentuk_sediaan' => 'Kapsul', 'satuan' => 'strip', 'kandungan' => 'Omeprazole 20 mg',
                'produsen' => 'Hexpharm Jaya', 'harga_beli' => 12500, 'harga_jual' => 17500,
                'stok' => 90, 'stok_minimum' => 25, 'kadaluarsa_bulan' => 19,
                'deskripsi' => 'Penghambat pompa proton untuk asam lambung berlebih.',
            ],
            [
                'kode_obat' => 'OBT-0404', 'nama' => 'Oralit Serbuk 200 ml',
                'kategori' => 'obat-pencernaan', 'supplier' => 0, 'golongan' => 'bebas',
                'bentuk_sediaan' => 'Serbuk', 'satuan' => 'sachet', 'kandungan' => 'NaCl, KCl, Natrium sitrat, Glukosa anhidrat',
                'produsen' => 'Kimia Farma', 'harga_beli' => 1200, 'harga_jual' => 2500,
                'stok' => 400, 'stok_minimum' => 100, 'kadaluarsa_bulan' => 30,
                'deskripsi' => 'Mencegah dehidrasi akibat diare.',
            ],
            [
                'kode_obat' => 'OBT-0405', 'nama' => 'Antimo Tablet',
                'kategori' => 'obat-pencernaan', 'supplier' => 3, 'golongan' => 'bebas_terbatas',
                'bentuk_sediaan' => 'Tablet', 'satuan' => 'strip', 'kandungan' => 'Dimenhydrinate 50 mg',
                'produsen' => 'Phapros', 'harga_beli' => 4800, 'harga_jual' => 7000,
                'stok' => 110, 'stok_minimum' => 25, 'kadaluarsa_bulan' => 21,
                'deskripsi' => 'Mencegah mabuk perjalanan, mual, dan muntah.',
            ],

            // --------------------------------------- Antihistamin & Alergi
            [
                'kode_obat' => 'OBT-0501', 'nama' => 'Cetirizine 10 mg',
                'kategori' => 'antihistamin-alergi', 'supplier' => 1, 'golongan' => 'keras',
                'bentuk_sediaan' => 'Tablet salut selaput', 'satuan' => 'strip', 'kandungan' => 'Cetirizine dihydrochloride 10 mg',
                'produsen' => 'Novell Pharmaceutical', 'harga_beli' => 6500, 'harga_jual' => 9500,
                'stok' => 150, 'stok_minimum' => 30, 'kadaluarsa_bulan' => 23,
                'deskripsi' => 'Antihistamin generasi kedua untuk alergi dan gatal.',
            ],
            [
                'kode_obat' => 'OBT-0502', 'nama' => 'Loratadine 10 mg',
                'kategori' => 'antihistamin-alergi', 'supplier' => 1, 'golongan' => 'keras',
                'bentuk_sediaan' => 'Tablet', 'satuan' => 'strip', 'kandungan' => 'Loratadine 10 mg',
                'produsen' => 'Dexa Medica', 'harga_beli' => 7200, 'harga_jual' => 10500,
                'stok' => 75, 'stok_minimum' => 20, 'kadaluarsa_bulan' => 17,
                'deskripsi' => 'Antialergi non-sedatif, tidak menyebabkan kantuk berat.',
            ],
            [
                'kode_obat' => 'OBT-0503', 'nama' => 'CTM 4 mg',
                'kategori' => 'antihistamin-alergi', 'supplier' => 0, 'golongan' => 'bebas_terbatas',
                'bentuk_sediaan' => 'Tablet', 'satuan' => 'strip', 'kandungan' => 'Chlorpheniramine maleate 4 mg',
                'produsen' => 'Indofarma', 'harga_beli' => 1800, 'harga_jual' => 3500,
                'stok' => 260, 'stok_minimum' => 50, 'kadaluarsa_bulan' => 26,
                'deskripsi' => 'Antihistamin klasik. Dapat menyebabkan kantuk.',
            ],

            // ------------------------------------------ Obat Luar & Topikal
            [
                'kode_obat' => 'OBT-0601', 'nama' => 'Betadine Antiseptik 60 ml',
                'kategori' => 'obat-luar-topikal', 'supplier' => 2, 'golongan' => 'bebas',
                'bentuk_sediaan' => 'Larutan', 'satuan' => 'botol', 'kandungan' => 'Povidone iodine 10%',
                'produsen' => 'Mahakam Beta Farma', 'harga_beli' => 22000, 'harga_jual' => 29000,
                'stok' => 65, 'stok_minimum' => 15, 'kadaluarsa_bulan' => 27,
                'deskripsi' => 'Antiseptik untuk luka luar.',
            ],
            [
                'kode_obat' => 'OBT-0602', 'nama' => 'Counterpain Cream 30 g',
                'kategori' => 'obat-luar-topikal', 'supplier' => 3, 'golongan' => 'bebas',
                'bentuk_sediaan' => 'Krim', 'satuan' => 'tube', 'kandungan' => 'Metil salisilat, Eugenol, Mentol',
                'produsen' => 'Taisho Pharmaceutical', 'harga_beli' => 28000, 'harga_jual' => 36000,
                'stok' => 48, 'stok_minimum' => 12, 'kadaluarsa_bulan' => 20,
                'deskripsi' => 'Meredakan nyeri otot dan pegal linu.',
            ],
            [
                'kode_obat' => 'OBT-0603', 'nama' => 'Daktarin Krim 10 g',
                'kategori' => 'obat-luar-topikal', 'supplier' => 2, 'golongan' => 'bebas_terbatas',
                'bentuk_sediaan' => 'Krim', 'satuan' => 'tube', 'kandungan' => 'Miconazole nitrate 2%',
                'produsen' => 'Janssen Pharmaceutica', 'harga_beli' => 38000, 'harga_jual' => 48000,
                'stok' => 30, 'stok_minimum' => 10, 'kadaluarsa_bulan' => 16,
                'deskripsi' => 'Antijamur kulit (panu, kadas, kurap).',
            ],
            [
                'kode_obat' => 'OBT-0604', 'nama' => 'Alkohol 70% 100 ml',
                'kategori' => 'obat-luar-topikal', 'supplier' => 0, 'golongan' => 'bebas',
                'bentuk_sediaan' => 'Larutan', 'satuan' => 'botol', 'kandungan' => 'Etanol 70%',
                'produsen' => 'OneMed', 'harga_beli' => 6000, 'harga_jual' => 9000,
                'stok' => 140, 'stok_minimum' => 30, 'kadaluarsa_bulan' => 29,
                'deskripsi' => 'Antiseptik pembersih kulit sebelum tindakan.',
            ],

            // ------------------------------------------------ Kardiovaskular
            [
                'kode_obat' => 'OBT-0701', 'nama' => 'Amlodipine 10 mg',
                'kategori' => 'kardiovaskular', 'supplier' => 1, 'golongan' => 'keras',
                'bentuk_sediaan' => 'Tablet', 'satuan' => 'strip', 'kandungan' => 'Amlodipine besylate 10 mg',
                'produsen' => 'Dexa Medica', 'harga_beli' => 9500, 'harga_jual' => 13500,
                'stok' => 175, 'stok_minimum' => 40, 'kadaluarsa_bulan' => 21,
                'deskripsi' => 'Obat darah tinggi golongan CCB. Wajib resep dokter.',
            ],
            [
                'kode_obat' => 'OBT-0702', 'nama' => 'Captopril 25 mg',
                'kategori' => 'kardiovaskular', 'supplier' => 0, 'golongan' => 'keras',
                'bentuk_sediaan' => 'Tablet', 'satuan' => 'strip', 'kandungan' => 'Captopril 25 mg',
                'produsen' => 'Kimia Farma', 'harga_beli' => 5500, 'harga_jual' => 8000,
                'stok' => 95, 'stok_minimum' => 25, 'kadaluarsa_bulan' => 13,
                'deskripsi' => 'Antihipertensi golongan ACE inhibitor.',
            ],
            [
                'kode_obat' => 'OBT-0703', 'nama' => 'Simvastatin 20 mg',
                'kategori' => 'kardiovaskular', 'supplier' => 1, 'golongan' => 'keras',
                'bentuk_sediaan' => 'Tablet salut selaput', 'satuan' => 'strip', 'kandungan' => 'Simvastatin 20 mg',
                'produsen' => 'Hexpharm Jaya', 'harga_beli' => 11000, 'harga_jual' => 15000,
                'stok' => 22, 'stok_minimum' => 25, 'kadaluarsa_bulan' => 12,
                'deskripsi' => 'Penurun kolesterol. Diminum malam hari.',
            ],

            // --------------------------------------------------- Antidiabetes
            [
                'kode_obat' => 'OBT-0801', 'nama' => 'Metformin 500 mg',
                'kategori' => 'antidiabetes', 'supplier' => 0, 'golongan' => 'keras',
                'bentuk_sediaan' => 'Tablet salut selaput', 'satuan' => 'strip', 'kandungan' => 'Metformin HCl 500 mg',
                'produsen' => 'Indofarma', 'harga_beli' => 6800, 'harga_jual' => 9800,
                'stok' => 165, 'stok_minimum' => 35, 'kadaluarsa_bulan' => 20,
                'deskripsi' => 'Antidiabetes oral lini pertama. Diminum bersama makan.',
            ],
            [
                'kode_obat' => 'OBT-0802', 'nama' => 'Glibenclamide 5 mg',
                'kategori' => 'antidiabetes', 'supplier' => 4, 'golongan' => 'keras',
                'bentuk_sediaan' => 'Tablet', 'satuan' => 'strip', 'kandungan' => 'Glibenclamide 5 mg',
                'produsen' => 'Phapros', 'harga_beli' => 7500, 'harga_jual' => 10500,
                'stok' => 58, 'stok_minimum' => 20, 'kadaluarsa_bulan' => 14,
                'deskripsi' => 'Sulfonilurea penurun gula darah.',
            ],

            // -------------------------------------------------- Obat Herbal
            [
                'kode_obat' => 'OBT-0901', 'nama' => 'Tolak Angin Cair 15 ml',
                'kategori' => 'obat-herbal', 'supplier' => 3, 'golongan' => 'herbal',
                'bentuk_sediaan' => 'Cairan obat dalam', 'satuan' => 'sachet', 'kandungan' => 'Ekstrak jahe, daun mint, madu',
                'produsen' => 'Sido Muncul', 'harga_beli' => 3200, 'harga_jual' => 5000,
                'stok' => 320, 'stok_minimum' => 60, 'kadaluarsa_bulan' => 22,
                'deskripsi' => 'Jamu untuk masuk angin dan perut kembung.',
            ],
            [
                'kode_obat' => 'OBT-0902', 'nama' => 'Antangin JRG 15 ml',
                'kategori' => 'obat-herbal', 'supplier' => 3, 'golongan' => 'herbal',
                'bentuk_sediaan' => 'Cairan obat dalam', 'satuan' => 'sachet', 'kandungan' => 'Jahe, Royal jelly, Ginseng',
                'produsen' => 'Deltomed Laboratories', 'harga_beli' => 3000, 'harga_jual' => 4800,
                'stok' => 280, 'stok_minimum' => 60, 'kadaluarsa_bulan' => 19,
                'deskripsi' => 'Membantu meredakan gejala masuk angin.',
            ],
            [
                'kode_obat' => 'OBT-0903', 'nama' => 'Diapet Kapsul',
                'kategori' => 'obat-herbal', 'supplier' => 4, 'golongan' => 'herbal',
                'bentuk_sediaan' => 'Kapsul', 'satuan' => 'strip', 'kandungan' => 'Ekstrak daun jambu biji, kunyit, buah mojokeling',
                'produsen' => 'Soho Industri Pharmasi', 'harga_beli' => 9800, 'harga_jual' => 13500,
                'stok' => 66, 'stok_minimum' => 20, 'kadaluarsa_bulan' => 18,
                'deskripsi' => 'Membantu mengurangi frekuensi buang air besar.',
            ],

            // ----------------------------------------------- Alat Kesehatan
            [
                'kode_obat' => 'ALK-0001', 'nama' => 'Masker Medis 3 Ply (Box isi 50)',
                'kategori' => 'alat-kesehatan', 'supplier' => 4, 'golongan' => 'bebas',
                'bentuk_sediaan' => null, 'satuan' => 'box', 'kandungan' => null,
                'produsen' => 'OneMed', 'harga_beli' => 24000, 'harga_jual' => 35000,
                'stok' => 52, 'stok_minimum' => 15, 'kadaluarsa_bulan' => 34,
                'deskripsi' => 'Masker bedah sekali pakai 3 lapis.',
            ],
            [
                'kode_obat' => 'ALK-0002', 'nama' => 'Hansaplast Plester (Box isi 10)',
                'kategori' => 'alat-kesehatan', 'supplier' => 2, 'golongan' => 'bebas',
                'bentuk_sediaan' => null, 'satuan' => 'box', 'kandungan' => null,
                'produsen' => 'Beiersdorf Indonesia', 'harga_beli' => 8500, 'harga_jual' => 12000,
                'stok' => 90, 'stok_minimum' => 20, 'kadaluarsa_bulan' => 32,
                'deskripsi' => 'Plester luka tahan air.',
            ],
            [
                'kode_obat' => 'ALK-0003', 'nama' => 'Termometer Digital',
                'kategori' => 'alat-kesehatan', 'supplier' => 4, 'golongan' => 'bebas',
                'bentuk_sediaan' => null, 'satuan' => 'pcs', 'kandungan' => null,
                'produsen' => 'OneMed', 'harga_beli' => 32000, 'harga_jual' => 45000,
                'stok' => 8, 'stok_minimum' => 10, 'kadaluarsa_bulan' => 46,
                'deskripsi' => 'Termometer digital ketiak, hasil cepat 60 detik.',
            ],
            [
                'kode_obat' => 'ALK-0004', 'nama' => 'Kasa Steril 16x16 cm',
                'kategori' => 'alat-kesehatan', 'supplier' => 4, 'golongan' => 'bebas',
                'bentuk_sediaan' => null, 'satuan' => 'pcs', 'kandungan' => null,
                'produsen' => 'Husada', 'harga_beli' => 2500, 'harga_jual' => 4000,
                'stok' => 175, 'stok_minimum' => 40, 'kadaluarsa_bulan' => 25,
                'deskripsi' => 'Kasa steril untuk menutup luka.',
            ],
        ];
    }
}
