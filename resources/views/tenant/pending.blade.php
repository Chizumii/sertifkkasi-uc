{{-- Halaman untuk penyewa yang pendaftarannya masih menunggu atau ditolak. Tidak ada aksi sewa. --}}
@extends('layouts.app')

@use('App\Enums\UserStatus')

@section('title', 'Status Pendaftaran')

@section('content')
    <div class="grid max-w-2xl gap-6">
        @if ($user->verification_status === UserStatus::Rejected)
            <x-page-header title="Pendaftaran ditolak" description="Admin tidak dapat memverifikasi data KTP yang Anda kirim." />
            <x-card>
                <div class="grid gap-3">
                    <x-badge :status="$user->verification_status" class="justify-self-start" />
                    <p>Akun ini tidak bisa dipakai untuk menyewa garasi dan penolakan tidak bisa dibatalkan. Silakan daftar ulang dengan data KTP yang benar. NIK dan email yang sama boleh dipakai lagi; akun yang ditolak ini akan diganti.</p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <input type="hidden" name="next" value="register">
                        <x-button>Keluar dan daftar ulang</x-button>
                    </form>
                </div>
            </x-card>
        @else
            <x-page-header title="Pendaftaran sedang diperiksa" description="Admin sedang mencocokkan data Anda dengan KTP." />
            <x-card>
                <div class="grid gap-3">
                    <x-badge :status="$user->verification_status" class="justify-self-start" />
                    <p>Setelah pendaftaran diterima, menu sewa garasi terbuka dan Anda bisa langsung menyewa. Silakan buka halaman ini lagi nanti.</p>
                </div>
            </x-card>
        @endif

        <dl class="grid sm:grid-cols-2">
            <div class="grid gap-0.5 border-t border-rule py-3">
                <dt class="text-sm text-muted">Nama</dt>
                <dd>{{ $user->full_name }}</dd>
            </div>
            <div class="grid gap-0.5 border-t border-rule py-3">
                <dt class="text-sm text-muted">Email</dt>
                <dd class="break-all">{{ $user->email }}</dd>
            </div>
            <div class="grid gap-0.5 border-t border-rule py-3">
                <dt class="text-sm text-muted">Nomor telepon</dt>
                <dd class="num">{{ $user->phone_number }}</dd>
            </div>
            <div class="grid gap-0.5 border-t border-rule py-3">
                <dt class="text-sm text-muted">Tanggal daftar</dt>
                <dd><x-date :value="$user->created_at" /></dd>
            </div>
        </dl>
    </div>
@endsection
