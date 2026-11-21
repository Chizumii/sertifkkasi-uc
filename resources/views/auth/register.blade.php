{{-- Halaman pendaftaran penyewa baru. Akun berstatus menunggu sampai diverifikasi admin. --}}
@extends('layouts.guest')

@section('title', 'Daftar')

@section('content')
    <x-card>
        <div class="mb-6 grid gap-1">
            <h1 class="text-2xl">Daftar sebagai penyewa</h1>
            <p class="text-muted">Akun akan diverifikasi admin sebelum bisa menyewa.</p>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="grid gap-2">
            @csrf
            <x-form-errors class="mb-3" />

            <x-input name="nik" label="NIK (nomor KTP)" inputmode="numeric" maxlength="16" pattern="[0-9]{16}" autocomplete="off" help="16 digit angka sesuai KTP." required />
            <x-input name="full_name" label="Nama lengkap" autocomplete="name" required />
            <x-input name="phone_number" type="tel" label="Nomor telepon" inputmode="tel" autocomplete="tel" placeholder="08123456789" required />
            <x-input name="email" type="email" label="Email" autocomplete="email" help="Email dipakai sebagai username saat masuk." required />
            <x-input name="password" type="password" label="Password" autocomplete="new-password" help="Minimal 8 karakter." required />
            <x-input name="password_confirmation" type="password" label="Ulangi password" autocomplete="new-password" required />

            <x-button class="mt-2 w-full">Daftar</x-button>
        </form>
    </x-card>

    <p class="text-center">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="text-link">Masuk</a>
    </p>
@endsection
