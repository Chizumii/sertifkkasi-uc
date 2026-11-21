<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi route hanya untuk role tertentu (alias: role). Contoh: middleware('role:admin').
 */
class EnsureUserHasRole
{
    /**
     * Meneruskan request bila role pengguna sesuai, selain itu menghentikan dengan 403.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  $role  Nilai enum UserRole, yaitu admin atau tenant.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        abort_unless($user !== null && $user->role === UserRole::from($role), 403, 'Anda tidak memiliki akses ke halaman ini.');

        return $next($request);
    }
}
