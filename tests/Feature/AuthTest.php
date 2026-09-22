<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/obat')->assertRedirect(route('login'));

        $this->get(route('login'))->assertOk()->assertSee('Masuk');
    }

    public function test_login_berhasil_masuk_ke_dashboard(): void
    {
        $kasir = User::factory()->kasir()->create();

        $this->post(route('login'), ['email' => $kasir->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($kasir);
        $this->assertNotNull($kasir->fresh()->terakhir_login);

        $this->get(route('dashboard'))->assertOk()->assertSee($kasir->name);
    }

    public function test_user_yang_sudah_login_tidak_bisa_membuka_halaman_login(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_login_gagal_dengan_pesan_yang_sama_untuk_email_atau_password_salah(): void
    {
        $user = User::factory()->create();

        $this->from(route('login'))
            ->post(route('login'), ['email' => $user->email, 'password' => 'salah'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Email atau password salah.']);

        $this->from(route('login'))
            ->post(route('login'), ['email' => 'tidak@ada.test', 'password' => 'salah'])
            ->assertSessionHasErrors(['email' => 'Email atau password salah.']);

        $this->assertGuest();
    }

    public function test_akun_nonaktif_tidak_bisa_login(): void
    {
        $user = User::factory()->nonaktif()->create();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_dibatasi_setelah_terlalu_banyak_percobaan(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $_) {
            $this->post(route('login'), ['email' => $user->email, 'password' => 'salah']);
        }

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Terlalu banyak percobaan login. Coba lagi dalam 1 menit.']);

        $this->assertGuest();
    }

    public function test_logout(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_user_yang_dinonaktifkan_langsung_dikeluarkan(): void
    {
        $kasir = User::factory()->kasir()->create();
        $this->actingAs($kasir)->get(route('dashboard'))->assertOk();

        $kasir->update(['aktif' => false]);

        $this->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_ganti_password(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('password.edit'))->assertOk();

        $this->from(route('password.edit'))->put(route('password.update'), [
            'password_lama' => 'salah',
            'password' => 'Baru12345',
            'password_confirmation' => 'Baru12345',
        ])->assertSessionHasErrors('password_lama');

        $this->put(route('password.update'), [
            'password_lama' => 'password',
            'password' => 'Baru12345',
            'password_confirmation' => 'Baru12345',
        ])->assertRedirect(route('dashboard'));

        $this->assertTrue(Hash::check('Baru12345', $user->fresh()->password));
    }
}
