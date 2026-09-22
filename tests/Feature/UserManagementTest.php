<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Obat;
use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_membuat_kasir_dan_kasir_itu_bisa_login(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('users.index'))->assertOk();
        $this->get(route('users.create'))->assertOk();

        $this->post(route('users.store'), [
            'name' => 'Kasir Malam',
            'email' => 'kasir3@apotek.test',
            'password' => 'Kasir12345',
            'role' => 'kasir',
            'aktif' => '1',
        ])->assertRedirect(route('users.index'));

        $kasir = User::where('email', 'kasir3@apotek.test')->firstOrFail();
        $this->assertTrue($kasir->isKasir());
        $this->assertTrue(Hash::check('Kasir12345', $kasir->password));

        auth()->logout();
        $this->post(route('login'), ['email' => 'kasir3@apotek.test', 'password' => 'Kasir12345'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_validasi_user(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'X',
            'email' => $admin->email,
            'password' => '123',
            'role' => 'apoteker',
        ])->assertSessionHasErrors(['email', 'password', 'role']);
    }

    public function test_ubah_user_tanpa_password_tidak_mengganti_password(): void
    {
        $kasir = User::factory()->kasir()->create();
        $hashLama = $kasir->password;

        $this->actingAs(User::factory()->admin()->create())->put(route('users.update', $kasir), [
            'name' => 'Nama Baru', 'email' => $kasir->email, 'role' => 'kasir', 'password' => '', 'aktif' => '1',
        ])->assertRedirect(route('users.index'));

        $kasir->refresh();
        $this->assertSame('Nama Baru', $kasir->name);
        $this->assertSame($hashLama, $kasir->password);
    }

    public function test_reset_password_oleh_admin_memutus_sesi_user_itu(): void
    {
        config(['session.driver' => 'database']);
        $kasir = User::factory()->kasir()->create();
        DB::table('sessions')->insert([
            'id' => 'sesi-kasir', 'user_id' => $kasir->id, 'ip_address' => '127.0.0.1',
            'user_agent' => 'test', 'payload' => '', 'last_activity' => time(),
        ]);

        $this->actingAs(User::factory()->admin()->create())->put(route('users.update', $kasir), [
            'name' => $kasir->name, 'email' => $kasir->email, 'role' => 'kasir', 'password' => 'Reset12345', 'aktif' => '1',
        ]);

        $this->assertDatabaseMissing('sessions', ['id' => 'sesi-kasir']);
    }

    public function test_admin_tidak_bisa_menghapus_menonaktifkan_atau_menurunkan_dirinya(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->delete(route('users.destroy', $admin))->assertSessionHas('error');
        $this->put(route('users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'kasir', 'aktif' => '1',
        ])->assertSessionHasErrors('role');
        $this->put(route('users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'admin', 'aktif' => '0',
        ])->assertSessionHasErrors('role');

        $admin->refresh();
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->aktif);
    }

    public function test_admin_bisa_menurunkan_admin_lain(): void
    {
        $adminLain = User::factory()->admin()->create();

        $this->actingAs(User::factory()->admin()->create())->put(route('users.update', $adminLain), [
            'name' => $adminLain->name, 'email' => $adminLain->email, 'role' => 'kasir', 'aktif' => '1',
        ])->assertRedirect(route('users.index'));

        $this->assertTrue($adminLain->fresh()->isKasir());
    }

    public function test_menghapus_kasir_tidak_menghapus_riwayat_penjualannya(): void
    {
        $kasir = User::factory()->kasir()->create();
        $obat = Obat::create([
            'kode_obat' => 'OBT-1', 'nama' => 'Paracetamol', 'kategori_id' => Kategori::create(['nama' => 'Umum'])->id,
            'harga_beli' => 1000, 'harga_jual' => 2000, 'stok' => 5,
        ]);
        $this->actingAs($kasir)->post(route('penjualan.store'), [
            'bayar' => 2000, 'items' => [['obat_id' => $obat->id, 'jumlah' => 1]],
        ]);
        $penjualan = Penjualan::sole();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('users.destroy', $kasir))
            ->assertRedirect(route('users.index'));

        $this->assertModelMissing($kasir);
        $this->assertNull($penjualan->fresh()->user_id);
    }

    public function test_kasir_tidak_bisa_mengelola_user(): void
    {
        $kasir = User::factory()->kasir()->create();
        $this->actingAs($kasir);

        $this->get(route('users.index'))->assertForbidden();
        $this->put(route('users.update', $kasir), [
            'name' => $kasir->name, 'email' => $kasir->email, 'role' => 'admin', 'aktif' => '1',
        ])->assertForbidden();

        $this->assertTrue($kasir->fresh()->isKasir());
    }
}
