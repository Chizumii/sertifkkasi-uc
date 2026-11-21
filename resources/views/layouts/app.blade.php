{{--
    Kerangka halaman setelah login: bilah atas dengan menu sesuai role, pesan flash, lalu konten halaman.
    Halaman mengisi @section('title') dan @section('content').
--}}
@php
    $menu = auth()->user()->isAdmin()
        ? [
            ['route' => 'admin.users.index', 'active' => 'admin.users.*', 'label' => 'Verifikasi'],
            ['route' => 'admin.garages.index', 'active' => 'admin.garages.*', 'label' => 'Garasi'],
            ['route' => 'admin.rentals.index', 'active' => 'admin.rentals.*', 'label' => 'Sewa Aktif'],
            ['route' => 'admin.reports.index', 'active' => 'admin.reports.*', 'label' => 'Laporan'],
        ]
        : (auth()->user()->isApproved() ? [
            ['route' => 'tenant.rentals.create', 'active' => 'tenant.rentals.create', 'label' => 'Sewa Garasi'],
            ['route' => 'tenant.rentals.index', 'active' => 'tenant.rentals.index', 'label' => 'Riwayat'],
        ] : [
            ['route' => 'tenant.status', 'active' => 'tenant.status', 'label' => 'Status Pendaftaran'],
        ]);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sewa garasi harian atau bulanan untuk sepeda, motor, mobil, dan mobil listrik.">
    <title>@yield('title') | Sewa Garasi</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
    <a href="#konten" class="sr-only rounded-input bg-ink px-4 py-2 text-paper focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50">Lewati ke konten</a>

    <header class="sticky top-0 z-40 border-b-2 border-ink bg-paper">
        <nav class="mx-auto flex min-h-16 max-w-app items-center gap-6 px-4 sm:px-6" aria-label="Menu utama">
            <a href="{{ route('home') }}" class="font-display text-lg font-bold tracking-tight text-ink no-underline" aria-label="Sewa Garasi, ke halaman awal">Sewa Garasi</a>

            <ul class="hidden items-center gap-1 lg:flex">
                @foreach ($menu as $item)
                    <li>
                        <a href="{{ route($item['route']) }}" class="nav-link" @if (request()->routeIs($item['active'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                    </li>
                @endforeach
            </ul>

            <div class="ml-auto hidden items-center gap-3 lg:flex">
                <span class="text-sm text-muted">{{ auth()->user()->full_name }}</span>
                <form method="POST" action="{{ route('logout') }}" data-confirm="Anda akan keluar dari akun ini. Masuk lagi kapan saja dengan email dan kata sandi Anda." data-confirm-title="Keluar dari akun?" data-confirm-button="Ya, keluar">
                    @csrf
                    <x-button variant="secondary">Keluar</x-button>
                </form>
            </div>

            <details class="relative ml-auto lg:hidden">
                <summary class="nav-link cursor-pointer list-none">Menu</summary>
                <div class="absolute right-0 z-50 mt-2 grid w-64 gap-1 rounded-card bg-surface p-3 shadow-lift">
                    <ul class="grid gap-1">
                        @foreach ($menu as $item)
                            <li>
                                <a href="{{ route($item['route']) }}" class="nav-link w-full" @if (request()->routeIs($item['active'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-2 grid gap-2 border-t border-rule pt-3">
                        <span class="px-3 text-sm text-muted">{{ auth()->user()->full_name }}</span>
                        <form method="POST" action="{{ route('logout') }}" data-confirm="Anda akan keluar dari akun ini. Masuk lagi kapan saja dengan email dan kata sandi Anda." data-confirm-title="Keluar dari akun?" data-confirm-button="Ya, keluar">
                            @csrf
                            <x-button variant="secondary" class="w-full">Keluar</x-button>
                        </form>
                    </div>
                </div>
            </details>
        </nav>
    </header>

    <main id="konten" class="mx-auto grid w-full max-w-app flex-1 content-start gap-8 px-4 py-8 sm:px-6 sm:py-10">
        @if (session('success'))
            <x-alert type="success">{{ session('success') }}</x-alert>
        @endif
        @if (session('error'))
            <x-alert type="error">{{ session('error') }}</x-alert>
        @endif
        @if (session('info'))
            <x-alert type="info">{{ session('info') }}</x-alert>
        @endif

        @yield('content')
    </main>

    @unless (auth()->user()->isAdmin())
        @php
            $adminContact = config('app.admin_contact');
        @endphp
        <footer class="mt-8 border-t-2 border-ink bg-paper-2">
            <div class="mx-auto grid max-w-app gap-6 px-4 py-8 sm:grid-cols-[1fr_auto] sm:px-6">
                <div class="grid gap-1">
                    <h2 class="text-lg">Butuh bantuan?</h2>
                    <p class="text-sm text-muted">Hubungi admin untuk pertanyaan pendaftaran, sewa, atau pembayaran. Sebutkan NIK Anda agar cepat diperiksa.</p>
                    <p class="text-sm text-muted">{{ $adminContact['hours'] }}</p>
                </div>
                <ul class="grid content-start gap-2 text-sm">
                    <li>
                        <span class="text-muted">WhatsApp</span>
                        <a href="https://wa.me/{{ $adminContact['whatsapp'] }}" class="text-link num" target="_blank" rel="noopener">{{ $adminContact['phone'] }}</a>
                    </li>
                    <li>
                        <span class="text-muted">Telepon</span>
                        <a href="tel:{{ $adminContact['phone'] }}" class="text-link num">{{ $adminContact['phone'] }}</a>
                    </li>
                    <li>
                        <span class="text-muted">Email</span>
                        <a href="mailto:{{ $adminContact['email'] }}" class="text-link break-all">{{ $adminContact['email'] }}</a>
                    </li>
                </ul>
            </div>
        </footer>
    @endunless

    <dialog id="confirm-dialog" class="confirm-dialog" aria-labelledby="confirm-dialog-title" aria-describedby="confirm-dialog-message">
        <form method="dialog" class="grid gap-4">
            <h2 id="confirm-dialog-title" class="text-xl">Konfirmasi</h2>
            <p id="confirm-dialog-message"></p>
            <div class="flex flex-wrap justify-end gap-2">
                <x-button variant="secondary" value="cancel" autofocus>Batal</x-button>
                <x-button variant="danger" value="confirm" data-confirm-accept>Ya, lanjutkan</x-button>
            </div>
        </form>
    </dialog>
</body>
</html>
