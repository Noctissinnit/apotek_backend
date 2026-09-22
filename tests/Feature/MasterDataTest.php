<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Obat;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_mengelola_kategori(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('kategori.index'))->assertOk();
        $this->get(route('kategori.create'))->assertOk();

        $this->post(route('kategori.store'), ['nama' => 'Obat Mata'])->assertRedirect(route('kategori.index'));
        $kategori = Kategori::where('nama', 'Obat Mata')->firstOrFail();
        $this->assertSame('obat-mata', $kategori->slug);

        $this->get(route('kategori.edit', $kategori))->assertOk();
        $this->put(route('kategori.update', $kategori), ['nama' => 'Obat Mata & Telinga'])->assertRedirect(route('kategori.index'));
        $this->assertSame('obat-mata-telinga', $kategori->fresh()->slug);

        $this->delete(route('kategori.destroy', $kategori))->assertRedirect(route('kategori.index'));
        $this->assertModelMissing($kategori);
    }

    public function test_kategori_yang_masih_dipakai_obat_tidak_bisa_dihapus(): void
    {
        $kategori = Kategori::create(['nama' => 'Antibiotik']);
        $obat = Obat::create([
            'kode_obat' => 'OBT-1', 'nama' => 'Amoxicillin', 'kategori_id' => $kategori->id,
            'harga_beli' => 1000, 'harga_jual' => 2000,
        ]);
        $obat->delete(); // walau obatnya sudah di-soft-delete

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('kategori.index'))
            ->delete(route('kategori.destroy', $kategori))
            ->assertRedirect(route('kategori.index'))
            ->assertSessionHas('error');

        $this->assertModelExists($kategori);
    }

    public function test_admin_mengelola_supplier(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('supplier.index'))->assertOk();

        $this->post(route('supplier.store'), [
            'nama' => 'PT Farma Jaya', 'telepon' => '021-123', 'email' => 'salah-format', 'aktif' => '1',
        ])->assertSessionHasErrors('email');

        $this->post(route('supplier.store'), [
            'nama' => 'PT Farma Jaya', 'telepon' => '021-123', 'email' => 'order@farma.test', 'aktif' => '1',
        ])->assertRedirect(route('supplier.index'));

        $supplier = Supplier::where('nama', 'PT Farma Jaya')->firstOrFail();
        $this->put(route('supplier.update', $supplier), ['nama' => 'PT Farma Jaya', 'aktif' => '0'])
            ->assertRedirect(route('supplier.index'));
        $this->assertFalse($supplier->fresh()->aktif);

        $this->delete(route('supplier.destroy', $supplier))->assertRedirect(route('supplier.index'));
        $this->assertModelMissing($supplier);
    }

    public function test_kasir_tidak_bisa_membuka_master_data(): void
    {
        $kategori = Kategori::create(['nama' => 'Vitamin']);
        $supplier = Supplier::create(['nama' => 'PT A']);
        $this->actingAs(User::factory()->kasir()->create());

        $this->get(route('kategori.index'))->assertForbidden();
        $this->post(route('kategori.store'), ['nama' => 'X'])->assertForbidden();
        $this->delete(route('kategori.destroy', $kategori))->assertForbidden();
        $this->get(route('supplier.index'))->assertForbidden();
        $this->delete(route('supplier.destroy', $supplier))->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
    }

    public function test_dashboard_menampilkan_data_sesuai_role(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('dashboard'))->assertOk()->assertSee('Omzet bulan ini')->assertSee('Nilai stok');

        $this->actingAs(User::factory()->kasir()->create())
            ->get(route('dashboard'))->assertOk()->assertDontSee('Omzet bulan ini')->assertDontSee('Nilai stok');
    }
}
