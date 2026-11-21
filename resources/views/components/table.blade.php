{{--
    Tabel data dengan empty state.
    - :empty="true": tampilkan judul dan penjelasan kosong, ditambah slot "action" (misalnya tombol) bila ada.
    - Selain itu: tabel bergaya .data-table. Slot "head" berisi <th>, isi utama berisi <tr>, slot "foot" (opsional) berisi <td> baris total.
    caption dibacakan pembaca layar sebagai judul tabel.
--}}
@props(['empty' => false, 'emptyTitle' => 'Belum ada data', 'emptyText' => null, 'caption' => null])

@if ($empty)
    <div {{ $attributes->class('grid justify-items-start gap-2 border-2 border-dashed border-rule bg-paper-2 p-6') }}>
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
        <table class="data-table w-full">
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
