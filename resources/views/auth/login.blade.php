{{-- Halaman masuk untuk admin dan penyewa. --}}
@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <x-card>
        <div class="mb-6 grid gap-1">
            <h1 class="text-2xl">Masuk ke akun Anda</h1>
            <p class="text-muted">Gunakan email dan password yang didaftarkan.</p>
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="grid gap-2">
            @csrf
            <x-form-errors class="mb-3" />

            <x-input name="email" type="email" label="Email" autocomplete="username" required autofocus />
            <x-input name="password" type="password" label="Password" autocomplete="current-password" required />

            <x-button class="mt-2 w-full">Masuk</x-button>
        </form>
    </x-card>

    <p class="text-center">
        Belum punya akun?
        <a href="{{ route('register') }}" class="text-link">Daftar sebagai penyewa</a>
    </p>
@endsection
