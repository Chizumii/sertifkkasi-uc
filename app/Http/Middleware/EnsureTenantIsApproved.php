<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Middleware ini buat mastiin penyewa sudah diterima admin sebelum boleh nambah kendaraan atau nyewa (nama pendeknya: approved).
class EnsureTenantIsApproved
{
    // Function ini buat ngelanjutin ke halaman tujuan kalau penyewa sudah diterima. Kalau belum, dialihkan ke halaman status pendaftaran.
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isApproved()) {
            return redirect()
                ->route('tenant.status')
                ->with('info', 'Fitur ini terbuka setelah pendaftaran Anda diterima admin.');
        }

        return $next($request);
    }
}
