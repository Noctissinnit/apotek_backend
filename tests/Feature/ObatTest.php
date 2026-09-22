<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Obat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObatTest extends TestCase
{
    use RefreshDatabase;

    private function buatObat(array $atribut = []): Obat
    {
        $kategori = Kategori::firstOrCreate(['nama' => 'Analgesik']);

        return Obat::create(array_merge([
            'kode_obat' => 'OBT-'.fake()->unique()->numberBetween(1000, 9999),
            'nama' => 'Paracetamol 500 mg',
            'kategori_id' => $kategori->id,
            'golongan' => 'bebas',
            'satuan' => 'strip',
            'harga_beli' => 3000,
            'harga_jual' => 5000,
            'stok' => 100,
            'stok_minimum' => 10,
        ], $atribut));
    }

    private function dataForm(array $timpa = []): array
    {
        return array_merge([
            'kode_obat' => 'OBT-BARU',
            'nama' => 'Ibuprofen 400 mg',
            'kategori_id' => Kategori::firstOrCreate(['nama' => 'Analgesik'])->id,
            'golongan' => 'bebas_terbatas',
            'satuan' => 'strip',
            'harga_beli' => 4000,
            'harga_jual' => 6500,
            'stok' => 30,
            'stok_minimum' => 10,
            'aktif' => '1',
        ], $timpa);
    }

    public function test_admin_dan_kasir_bisa_melihat_daftar_dan_detail_obat(): void
    {
        $obat = $this->buatObat(['nama' => 'Amoxicillin 500 mg']);

        foreach ([User::factory()->admin()->create(), User::factory()->kasir()->create()] as $user) {
            $this->actingAs($user)->get(route('obat.index'))->assertOk()->assertSee('Amoxicillin 500 mg');
            $this->actingAs($user)->get(route('obat.show', $obat))->assertOk()->assertSee($obat->kode_obat);
        }
    }

    public function test_harga_beli_hanya_terlihat_oleh_admin(): void
    {
        $obat = $this->buatObat(['harga_beli' => 3333]);

        $this->actingAs(User::factory()->admin()->create())->get(route('obat.show', $obat))->assertSee('Rp 3.333');
        $this->actingAs(User::factory()->kasir()->create())->get(route('obat.show', $obat))->assertDontSee('Rp 3.333');
    }

    public function test_pencarian_dan_filter_stok_menipis(): void
    {
        $this->buatObat(['nama' => 'Amoxicillin 500 mg', 'stok' => 5, 'stok_minimum' => 20]);
        $this->buatObat(['nama' => 'Vitamin C 1000', 'stok' => 200]);
        $this->actingAs(User::factory()->kasir()->create());

        $this->get(route('obat.index', ['search' => 'amoxi']))
            ->assertSee('Amoxicillin 500 mg')->assertDontSee('Vitamin C 1000');

        $this->get(route('obat.index', ['stok_menipis' => 1]))
            ->assertSee('Amoxicillin 500 mg')->assertDontSee('Vitamin C 1000');
    }

    public function test_admin_bisa_menambah_obat(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('obat.create'))->assertOk();

        $response = $this->post(route('obat.store'), $this->dataForm());

        $obat = Obat::where('kode_obat', 'OBT-BARU')->firstOrFail();
        $response->assertRedirect(route('obat.show', $obat))->assertSessionHas('success');
        $this->assertSame(30, $obat->stok);
        $this->assertTrue($obat->aktif);
    }

    public function test_validasi_form_obat(): void
    {
        $this->buatObat(['kode_obat' => 'OBT-ADA']);
        $this->actingAs(User::factory()->admin()->create());

        $this->from(route('obat.create'))
            ->post(route('obat.store'), $this->dataForm(['kode_obat' => 'OBT-ADA', 'harga_beli' => 9000, 'harga_jual' => 5000]))
            ->assertRedirect(route('obat.create'))
            ->assertSessionHasErrors(['kode_obat', 'harga_jual']);
    }

    public function test_mengubah_obat_tidak_mengubah_stok(): void
    {
        $obat = $this->buatObat(['stok' => 40]);
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('obat.edit', $obat))->assertOk();

        $this->put(route('obat.update', $obat), $this->dataForm([
            'kode_obat' => $obat->kode_obat,
            'nama' => 'Paracetamol Sirup',
            'stok' => 999,
            'aktif' => '0',
        ]))->assertRedirect(route('obat.show', $obat));

        $obat->refresh();
        $this->assertSame('Paracetamol Sirup', $obat->nama);
        $this->assertSame(40, $obat->stok);
        $this->assertFalse($obat->aktif);
    }

    public function test_atur_stok_tambah_kurang_dan_opname(): void
    {
        $obat = $this->buatObat(['stok' => 10]);
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('obat.stok.edit', $obat))->assertOk();

        $this->put(route('obat.stok.update', $obat), ['tipe' => 'tambah', 'jumlah' => 5])->assertSessionHas('success');
        $this->assertSame(15, $obat->fresh()->stok);

        $this->put(route('obat.stok.update', $obat), ['tipe' => 'kurang', 'jumlah' => 3]);
        $this->assertSame(12, $obat->fresh()->stok);

        $this->put(route('obat.stok.update', $obat), ['tipe' => 'set', 'jumlah' => 50]);
        $this->assertSame(50, $obat->fresh()->stok);
    }

    public function test_stok_tidak_boleh_minus(): void
    {
        $obat = $this->buatObat(['stok' => 10]);
        $this->actingAs(User::factory()->admin()->create());

        $this->from(route('obat.stok.edit', $obat))
            ->put(route('obat.stok.update', $obat), ['tipe' => 'kurang', 'jumlah' => 11])
            ->assertRedirect(route('obat.stok.edit', $obat))
            ->assertSessionHasErrors('jumlah');

        $this->assertSame(10, $obat->fresh()->stok);
    }

    public function test_hapus_obat_memakai_soft_delete(): void
    {
        $obat = $this->buatObat();
        $this->actingAs(User::factory()->admin()->create());

        $this->delete(route('obat.destroy', $obat))->assertRedirect(route('obat.index'));

        $this->assertSoftDeleted($obat);
    }

    public function test_kasir_tidak_bisa_mengelola_obat(): void
    {
        $obat = $this->buatObat(['stok' => 10]);
        $this->actingAs(User::factory()->kasir()->create());

        $this->get(route('obat.create'))->assertForbidden();
        $this->post(route('obat.store'), $this->dataForm())->assertForbidden();
        $this->get(route('obat.edit', $obat))->assertForbidden();
        $this->put(route('obat.update', $obat), $this->dataForm())->assertForbidden();
        $this->put(route('obat.stok.update', $obat), ['tipe' => 'tambah', 'jumlah' => 5])->assertForbidden();
        $this->delete(route('obat.destroy', $obat))->assertForbidden();

        $this->assertSame(10, $obat->fresh()->stok);
        $this->assertNotSoftDeleted($obat);
    }
}
