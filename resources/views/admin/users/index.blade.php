{{-- Daftar pendaftar dengan filter status. Pendaftar yang menunggu bisa diterima atau ditolak. --}}
@extends('layouts.app')

@use('App\Enums\UserStatus')

@section('title', 'Verifikasi Pendaftar')

@section('content')
    <x-page-header title="Verifikasi pendaftar" description="Cocokkan NIK dan nama dengan KTP sebelum menerima pendaftaran." />

    <x-split-layout aside-label="Filter pendaftar" aside-width="16rem">
        <x-slot:aside>
            <nav aria-label="Filter status" class="grid gap-2">
                <p class="text-sm font-medium text-ink-2">Status pendaftaran</p>
                <ul class="tab-stack grid">
                    @foreach ([...UserStatus::cases(), null] as $filterStatus)
                        @php
                            $filterValue = $filterStatus?->value ?? 'all';
                            $filterCount = $filterStatus ? ($statusCounts[$filterStatus->value] ?? 0) : array_sum($statusCounts);
                        @endphp
                        <li>
                            <a
                                href="{{ route('admin.users.index', ['status' => $filterValue]) }}"
                                class="tab"
                                @if ($status === $filterValue) aria-current="page" @endif
                            >
                                <span>{{ $filterStatus?->label() ?? 'Semua' }}</span>
                                <span class="num text-muted">{{ $filterCount }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
            <p class="text-sm text-muted">Keputusan bersifat final. Pendaftar yang ditolak harus daftar ulang dengan data yang benar.</p>
        </x-slot:aside>

        <x-table
            :empty="$users->isEmpty()"
            empty-title="Tidak ada pendaftar dengan status ini"
            empty-text="Pendaftar baru otomatis masuk ke tab Menunggu Verifikasi."
            caption="Daftar pendaftar"
        >
            <x-slot:head>
                <th scope="col">Penyewa</th>
                <th scope="col">NIK dan telepon</th>
                <th scope="col">Status dan aksi</th>
            </x-slot:head>
            @foreach ($users as $user)
                <tr>
                    <td class="min-w-44">
                        <span class="font-medium">{{ $user->full_name }}</span>
                        <span class="block text-sm break-words text-muted">{{ $user->email }}</span>
                        <span class="block text-sm text-muted">Daftar <x-date :value="$user->created_at" /></span>
                    </td>
                    <td class="whitespace-nowrap">
                        <span class="font-mono">{{ $user->nik }}</span>
                        <span class="num block text-sm text-muted">{{ $user->phone_number }}</span>
                    </td>
                    <td>
                        <div class="grid justify-items-start gap-2">
                            <x-badge :status="$user->verification_status" />
                            @if ($user->verification_status === UserStatus::Pending)
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                                        @csrf
                                        <x-button variant="secondary">Terima</x-button>
                                    </form>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.users.reject', $user) }}"
                                        data-confirm="Pendaftaran {{ $user->full_name }} (NIK {{ $user->nik }}) akan ditolak. Penolakan tidak bisa dibatalkan; penyewa harus daftar ulang dengan data yang benar."
                                        data-confirm-title="Tolak pendaftaran?"
                                        data-confirm-button="Ya, tolak"
                                    >
                                        @csrf
                                        <x-button variant="danger">Tolak</x-button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>

        {{ $users->links() }}
    </x-split-layout>
@endsection
