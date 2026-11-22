<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Middleware ini buat ngebatasin halaman supaya cuma bisa dibuka peran tertentu (nama pendeknya: role). Contoh: middleware('role:admin').
class EnsureUserHasRole
{
    // Function ini buat ngelanjutin ke halaman tujuan kalau perannya cocok. Kalau nggak cocok, ditolak dengan error 403.
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        abort_unless($user !== null && $user->role === UserRole::from($role), 403, 'Anda tidak memiliki akses ke halaman ini.');

        return $next($request);
    }
}
