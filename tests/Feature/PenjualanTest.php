<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Obat;
use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenjualanTest extends TestCase
{
    use RefreshDatabase;

    private function buatObat(array $atribut = []): Obat
    {
        $kategori = Kategori::firstOrCreate(['nama' => 'Umum']);

        return Obat::create(array_merge([
            'kode_obat' => 'OBT-'.fake()->unique()->numberBetween(1000, 9999),
            'nama' => 'Paracetamol',
            'kategori_id' => $kategori->id,
            'harga_beli' => 3000,
            'harga_jual' => 5000,
            'stok' => 20,
        ], $atribut));
    }

    private function jual(Obat $obat, int $jumlah = 1, float $bayar = 100000)
    {
        return $this->post(route('penjualan.store'), [
            'bayar' => $bayar,
            'items' => [['obat_id' => $obat->id, 'jumlah' => $jumlah]],
        ]);
    }

    public function test_halaman_kasir_hanya_menampilkan_obat_aktif_yang_ada_stoknya(): void
    {
        $this->buatObat(['nama' => 'Obat Tersedia']);
        $this->buatObat(['nama' => 'Obat Habis', 'stok' => 0]);
        $this->buatObat(['nama' => 'Obat Nonaktif', 'aktif' => false]);

        $this->actingAs(User::factory()->kasir()->create())
            ->get(route('penjualan.create'))
            ->assertOk()
            ->assertSee('Obat Tersedia')
            ->assertDontSee('Obat Habis')
            ->assertDontSee('Obat Nonaktif');
    }

    public function test_transaksi_mengurangi_stok_dan_tercatat_atas_nama_kasir(): void
    {
        $kasir = User::factory()->kasir()->create(['name' => 'Kasir Pagi']);
        $obat = $this->buatObat(['stok' => 20, 'harga_jual' => 5000]);

        $response = $this->actingAs($kasir)->post(route('penjualan.store'), [
            'nama_pelanggan' => 'Ibu Dewi',
            'metode_bayar' => 'qris',
            'bayar' => 20000,
            'items' => [
                ['obat_id' => $obat->id, 'jumlah' => 2],
                ['obat_id' => $obat->id, 'jumlah' => 1],   // obat sama digabung
                ['obat_id' => '', 'jumlah' => 1],          // baris kosong diabaikan
            ],
        ]);

        $penjualan = Penjualan::with('detail')->sole();
        $response->assertRedirect(route('penjualan.show', $penjualan))->assertSessionHas('success');

        $this->assertSame($kasir->id, $penjualan->user_id);
        $this->assertEquals(15000, $penjualan->total);
        $this->assertEquals(5000, $penjualan->kembalian);
        $this->assertCount(1, $penjualan->detail);
        $this->assertSame(3, $penjualan->detail->first()->jumlah);
        $this->assertSame(17, $obat->fresh()->stok);

        $this->get(route('penjualan.show', $penjualan))
            ->assertOk()
            ->assertSee($penjualan->kode_transaksi)
            ->assertSee('Kasir Pagi')
            ->assertSee('Rp 15.000');
    }

    public function test_harga_selalu_diambil_dari_master_obat(): void
    {
        $obat = $this->buatObat(['harga_jual' => 5000]);

        $this->actingAs(User::factory()->kasir()->create())->post(route('penjualan.store'), [
            'bayar' => 5000,
            'items' => [['obat_id' => $obat->id, 'jumlah' => 1, 'harga_satuan' => 1]],
        ]);

        $this->assertEquals(5000, Penjualan::sole()->total);
    }

    public function test_transaksi_ditolak_kalau_stok_kurang(): void
    {
        $obat = $this->buatObat(['stok' => 2]);

        $this->actingAs(User::factory()->kasir()->create())
            ->from(route('penjualan.create'))
            ->post(route('penjualan.store'), ['bayar' => 999999, 'items' => [['obat_id' => $obat->id, 'jumlah' => 5]]])
            ->assertRedirect(route('penjualan.create'))
            ->assertSessionHasErrors('items');

        $this->assertSame(2, $obat->fresh()->stok);
        $this->assertSame(0, Penjualan::count());
    }

    public function test_transaksi_ditolak_kalau_uang_kurang(): void
    {
        $obat = $this->buatObat(['harga_jual' => 5000, 'stok' => 10]);

        $this->actingAs(User::factory()->kasir()->create())
            ->from(route('penjualan.create'))
            ->post(route('penjualan.store'), ['bayar' => 1000, 'items' => [['obat_id' => $obat->id, 'jumlah' => 1]]])
            ->assertSessionHasErrors('bayar');

        $this->assertSame(10, $obat->fresh()->stok);
        $this->assertSame(0, Penjualan::count());
    }

    public function test_transaksi_tanpa_item_ditolak(): void
    {
        $this->actingAs(User::factory()->kasir()->create())
            ->post(route('penjualan.store'), ['bayar' => 1000, 'items' => [['obat_id' => '', 'jumlah' => 1]]])
            ->assertSessionHasErrors('items');
    }

    public function test_kasir_hanya_melihat_transaksinya_sendiri(): void
    {
        $obat = $this->buatObat();
        $kasirPagi = User::factory()->kasir()->create();
        $kasirSore = User::factory()->kasir()->create();

        $this->actingAs($kasirPagi);
        $this->jual($obat);
        $milikPagi = Penjualan::sole();

        $this->actingAs($kasirSore);
        $this->jual($obat);
        $milikSore = Penjualan::where('user_id', $kasirSore->id)->sole();

        $this->get(route('penjualan.index'))
            ->assertOk()
            ->assertSee($milikSore->kode_transaksi)
            ->assertDontSee($milikPagi->kode_transaksi);

        $this->get(route('penjualan.show', $milikPagi))->assertNotFound();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('penjualan.index'))
            ->assertSee($milikPagi->kode_transaksi)
            ->assertSee($milikSore->kode_transaksi);
    }

    public function test_admin_membatalkan_transaksi_dan_stok_kembali(): void
    {
        $obat = $this->buatObat(['stok' => 10]);
        $kasir = User::factory()->kasir()->create();

        $this->actingAs($kasir);
        $this->jual($obat, 4);
        $penjualan = Penjualan::sole();
        $this->assertSame(6, $obat->fresh()->stok);

        $this->delete(route('penjualan.destroy', $penjualan))->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('penjualan.destroy', $penjualan))
            ->assertRedirect(route('penjualan.index'));

        $this->assertSame(10, $obat->fresh()->stok);
        $this->assertModelMissing($penjualan);
    }
}
