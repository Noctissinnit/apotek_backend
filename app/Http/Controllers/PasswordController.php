<?php

namespace App\Http\Controllers;

use App\Http\Requests\GantiPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        return view('profil.password');
    }

    /** Perangkat yang sedang dipakai tetap login; sesi di perangkat lain diputus. */
    public function update(GantiPasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->update(['password' => $request->validated('password')]);

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        return redirect()->route('dashboard')->with('success', 'Password berhasil diganti.');
    }
}
