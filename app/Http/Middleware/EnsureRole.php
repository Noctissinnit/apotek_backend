<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pemakaian di route: ->middleware('role:admin') atau 'role:admin,kasir'.
 * Harus dipasang setelah middleware auth.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_if(
            ! $user || ! $user->hasRole(...$roles),
            403,
            'Anda tidak punya akses ke halaman ini. Dibutuhkan role: '.implode(' / ', $roles).'.'
        );

        return $next($request);
    }
}
