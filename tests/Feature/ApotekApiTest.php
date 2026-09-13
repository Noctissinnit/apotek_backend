<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Obat;
use App\Models\Penjualan;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApotekApiTest extends TestCase
{
    use RefreshDatabase;

    private function buatObat(array $atribut = []): Obat
    {
        $kategori = Kategori::firstOrCreate(['nama' => 'Analgesik'], ['deskripsi' => null]);
        $supplier = Supplier::firstOrCreate(['nama' => 'PT Contoh Farma']);

        return Obat::create(array_merge([
            'kode_obat' => 'OBT-TEST-'.fake()->unique()->numberBetween(1000, 9999),
            'nama' => 'Paracetamol 500 mg',
            'kategori_id' => $kategori->id,
            'supplier_id' => $supplier->id,
            'golongan' => 'bebas',
            'satuan' => 'strip',
            'harga_beli' => 3000,
            'harga_jual' => 5000,
            'stok' => 100,
            'stok_minimum' => 10,
        ], $atribut));
    }

    public function test_daftar_obat_bisa_diakses_dan_berpaginasi(): void
    {
        $this->buatObat();

        $this->getJson('/api/obat')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => [['id', 'kode_obat', 'nama', 'harga_jual', 'stok', 'kategori']], 'meta' => ['total', 'current_page']]);
    }

    public function test_obat_bisa_dicari_dan_difilter_stok_menipis(): void
    {
        $this->buatObat(['nama' => 'Amoxicillin 500 mg', 'stok' => 5, 'stok_minimum' => 20]);
        $this->buatObat(['nama' => 'Vitamin C', 'stok' => 200, 'stok_minimum' => 20]);

        $this->getJson('/api/obat?search=amoxicillin')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama', 'Amoxicillin 500 mg');

        $this->getJson('/api/obat?stok_menipis=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.stok_menipis', true);
    }

    public function test_obat_baru_bisa_dibuat(): void
    {
        $obat = $this->buatObat();

        $this->postJson('/api/obat', [
            'kode_obat' => 'OBT-BARU-1',
            'nama' => 'Ibuprofen 400 mg',
            'kategori_id' => $obat->kategori_id,
            'harga_beli' => 4000,
            'harga_jual' => 6000,
            'stok' => 30,
        ])->assertCreated()->assertJsonPath('data.nama', 'Ibuprofen 400 mg');

        $this->assertDatabaseHas('obat', ['kode_obat' => 'OBT-BARU-1', 'stok' => 30]);
    }

    public function test_harga_jual_tidak_boleh_di_bawah_harga_beli(): void
    {
        $obat = $this->buatObat();

        $this->postJson('/api/obat', [
            'kode_obat' => 'OBT-RUGI-1',
            'nama' => 'Obat Rugi',
            'kategori_id' => $obat->kategori_id,
            'harga_beli' => 10000,
            'harga_jual' => 5000,
        ])->assertStatus(422)->assertJsonValidationErrors('harga_jual');
    }

    public function test_stok_bisa_ditambah_dan_tidak_boleh_minus(): void
    {
        $obat = $this->buatObat(['stok' => 10]);

        $this->patchJson("/api/obat/{$obat->id}/stok", ['tipe' => 'tambah', 'jumlah' => 5])
            ->assertOk()
            ->assertJsonPath('data.stok', 15);

        $this->patchJson("/api/obat/{$obat->id}/stok", ['tipe' => 'kurang', 'jumlah' => 999])
            ->assertStatus(422);

        $this->assertSame(15, $obat->fresh()->stok);
    }

    public function test_penjualan_mengurangi_stok_dan_menghitung_kembalian(): void
    {
        $obat = $this->buatObat(['stok' => 20, 'harga_jual' => 5000]);

        $response = $this->postJson('/api/penjualan', [
            'nama_pelanggan' => 'Ibu Dewi',
            'bayar' => 50000,
            'items' => [
                ['obat_id' => $obat->id, 'jumlah' => 2],
                ['obat_id' => $obat->id, 'jumlah' => 1],
            ],
        ])->assertCreated();

        $response->assertJsonPath('data.total', 15000)
            ->assertJsonPath('data.kembalian', 35000)
            ->assertJsonCount(1, 'data.detail')   // item duplikat digabung
            ->assertJsonPath('data.detail.0.jumlah', 3);

        $this->assertSame(17, $obat->fresh()->stok);
    }

    public function test_penjualan_ditolak_kalau_stok_kurang(): void
    {
        $obat = $this->buatObat(['stok' => 2]);

        $this->postJson('/api/penjualan', [
            'bayar' => 999999,
            'items' => [['obat_id' => $obat->id, 'jumlah' => 5]],
        ])->assertStatus(422)->assertJsonValidationErrors('items');

        $this->assertSame(2, $obat->fresh()->stok, 'Stok tidak boleh berubah kalau transaksi gagal.');
    }

    public function test_penjualan_ditolak_kalau_bayar_kurang(): void
    {
        $obat = $this->buatObat(['harga_jual' => 5000, 'stok' => 10]);

        $this->postJson('/api/penjualan', [
            'bayar' => 1000,
            'items' => [['obat_id' => $obat->id, 'jumlah' => 1]],
        ])->assertStatus(422)->assertJsonValidationErrors('bayar');

        $this->assertSame(10, $obat->fresh()->stok);
        $this->assertSame(0, Penjualan::count());
    }

    public function test_membatalkan_penjualan_mengembalikan_stok(): void
    {
        $obat = $this->buatObat(['stok' => 10, 'harga_jual' => 5000]);

        $id = $this->postJson('/api/penjualan', [
            'bayar' => 50000,
            'items' => [['obat_id' => $obat->id, 'jumlah' => 4]],
        ])->json('data.id');

        $this->assertSame(6, $obat->fresh()->stok);

        $this->deleteJson("/api/penjualan/{$id}")->assertOk();

        $this->assertSame(10, $obat->fresh()->stok);
        $this->assertDatabaseMissing('penjualan', ['id' => $id]);
    }

    public function test_endpoint_tulis_terkunci_kalau_api_key_diisi(): void
    {
        config(['apotek.api_key' => 'kunci-rahasia']);

        $obat = $this->buatObat();

        $this->postJson('/api/kategori', ['nama' => 'Tanpa Kunci'])->assertStatus(401);

        $this->withHeader('X-API-KEY', 'kunci-rahasia')
            ->postJson('/api/kategori', ['nama' => 'Dengan Kunci'])
            ->assertCreated();

        // Endpoint baca tetap terbuka.
        $this->getJson("/api/obat/{$obat->id}")->assertOk();
    }

    public function test_statistik_menampilkan_ringkasan(): void
    {
        $this->buatObat(['stok' => 5, 'stok_minimum' => 10]);

        $this->getJson('/api/statistik')
            ->assertOk()
            ->assertJsonPath('data.total_obat', 1)
            ->assertJsonPath('data.stok_menipis', 1)
            ->assertJsonStructure(['data' => ['nilai_stok', 'omzet_hari_ini', 'obat_terlaris']]);
    }

    public function test_data_tidak_ditemukan_menjawab_json_404(): void
    {
        $this->getJson('/api/obat/999999')
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }
}
