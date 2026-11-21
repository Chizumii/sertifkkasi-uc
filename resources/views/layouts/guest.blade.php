{{--
    Kerangka halaman tamu (login dan registrasi): nama aplikasi, pesan flash, lalu satu form di kolom sempit.
    Halaman mengisi @section('title') dan @section('content').
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sewa Garasi harian atau bulanan untuk sepeda, motor, mobil, dan mobil listrik.">
    <title>@yield('title') | Sewa Garasi</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper-2">
    <main class="mx-auto grid max-w-narrow gap-6 px-4 py-10 sm:py-16">
        <p class="font-display text-xl font-bold tracking-tight">Sewa Garasi</p>

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
</body>
</html>
