{{-- Pembungkus tabel responsif. Bila $empty bernilai true, menampilkan empty state beserta satu aksi (slot "action"). --}}
@props(['empty' => false, 'emptyTitle' => 'Belum ada data', 'emptyText' => null, 'caption' => null])

@if ($empty)
    <div {{ $attributes->class('grid justify-items-start gap-2 rounded-card bg-paper-2 px-5 py-8 sm:px-8') }}>
        <p class="text-lg font-medium">{{ $emptyTitle }}</p>
        @if ($emptyText)
            <p class="max-w-prose text-muted">{{ $emptyText }}</p>
        @endif
        @isset($action)
            <div class="mt-2">{{ $action }}</div>
        @endisset
    </div>
@else
    <div {{ $attributes->class('overflow-x-auto') }}>
        <table class="data-table">
            @if ($caption)
                <caption class="sr-only">{{ $caption }}</caption>
            @endif
            @isset($head)
                <thead>
                    <tr>{{ $head }}</tr>
                </thead>
            @endisset
            <tbody>
                {{ $slot }}
            </tbody>
            @isset($foot)
                <tfoot>
                    <tr>{{ $foot }}</tr>
                </tfoot>
            @endisset
        </table>
    </div>
@endif
