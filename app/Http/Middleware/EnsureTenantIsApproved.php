<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memastikan penyewa sudah diterima admin sebelum mengelola kendaraan atau menyewa (alias: approved).
 */
class EnsureTenantIsApproved
{
    /**
     * Meneruskan request bila pengguna berstatus approved, selain itu kembali ke dashboard penyewa dengan pesan.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isApproved()) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('info', 'Fitur ini terbuka setelah pendaftaran Anda diterima admin.');
        }

        return $next($request);
    }
}
