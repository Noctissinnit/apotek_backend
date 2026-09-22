<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Manajemen akun petugas. Seluruh halaman di sini khusus admin. */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->withCount('penjualan')
            ->when($request->filled('search'), function ($q) use ($request) {
                $like = '%'.$request->query('search').'%';
                $q->where(fn ($sub) => $sub->where('name', 'like', $like)->orWhere('email', 'like', $like));
            })
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->query('role')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('users.index', ['users' => $users]);
    }

    public function create(): View
    {
        return view('users.create', ['user' => new User(['role' => User::ROLE_KASIR, 'aktif' => true])]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create(array_merge($request->validated(), ['aktif' => $request->boolean('aktif')]));

        return redirect()->route('users.index')->with('success', "User {$user->name} berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        return view('users.edit', ['user' => $user]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = array_merge($request->validated(), ['aktif' => $request->boolean('aktif')]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $turunDariAdmin = $user->isAdmin() && ($data['role'] ?? $user->role) !== User::ROLE_ADMIN;
        $dinonaktifkan = $user->aktif && ! $data['aktif'];

        if ($user->is($request->user()) && ($turunDariAdmin || $dinonaktifkan)) {
            throw ValidationException::withMessages([
                'role' => 'Anda tidak bisa menurunkan role atau menonaktifkan akun Anda sendiri.',
            ]);
        }

        DB::transaction(function () use ($user, $data, $turunDariAdmin, $dinonaktifkan) {
            if (($turunDariAdmin || $dinonaktifkan) && $user->isAdmin()) {
                $this->pastikanMasihAdaAdminLain($user);
            }

            $user->update($data);
        });

        // Password direset admin: paksa user itu login ulang di semua perangkat.
        if (isset($data['password'])) {
            $this->putuskanSesi($user);
        }

        return redirect()->route('users.index')->with('success', "User {$user->name} berhasil diperbarui.");
    }

    /** Riwayat penjualan kasir tetap tersimpan (penjualan.user_id jadi kosong). */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        try {
            DB::transaction(function () use ($user) {
                if ($user->isAdmin()) {
                    $this->pastikanMasihAdaAdminLain($user);
                }

                $user->delete();
            });
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->putuskanSesi($user);

        return redirect()->route('users.index')->with('success', "User {$user->name} berhasil dihapus.");
    }

    /** Apotek tidak boleh kehilangan admin aktif terakhir. */
    private function pastikanMasihAdaAdminLain(User $user): void
    {
        $adminLain = User::adminAktif()
            ->whereKeyNot($user->id)
            ->lockForUpdate()
            ->count();

        if ($adminLain === 0) {
            throw ValidationException::withMessages(['role' => 'Harus tersisa minimal satu admin aktif.']);
        }
    }

    private function putuskanSesi(User $user): void
    {
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }
    }
}
