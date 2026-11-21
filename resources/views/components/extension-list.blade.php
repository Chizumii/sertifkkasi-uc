{{-- Riwayat perpanjangan sebuah sewa: tanggal selesai lama dan baru, biaya tambahan, dan bukti bayar. --}}
@props(['extensions'])

@use('Illuminate\Support\Facades\Storage')

<section {{ $attributes->class('grid gap-3') }} aria-labelledby="extension-history">
    <h2 id="extension-history" class="text-lg">Riwayat perpanjangan</h2>
    <x-table
        :empty="$extensions->isEmpty()"
        empty-title="Belum pernah diperpanjang"
        empty-text="Perpanjangan dicatat oleh admin dan akan muncul di sini bersama bukti bayarnya."
        caption="Riwayat perpanjangan sewa"
    >
        <x-slot:head>
            <th scope="col">Tanggal dicatat</th>
            <th scope="col">Selesai lama</th>
            <th scope="col">Selesai baru</th>
            <th scope="col" class="text-right">Biaya tambahan</th>
            <th scope="col">Bukti bayar</th>
        </x-slot:head>
        @foreach ($extensions as $extension)
            <tr>
                <td>
                    <x-date :value="$extension->created_at" />
                    @if ($extension->relationLoaded('admin'))
                        <span class="block text-sm text-muted">oleh {{ $extension->admin->full_name }}</span>
                    @endif
                </td>
                <td><x-date :value="$extension->previous_end_date" /></td>
                <td>
                    <x-date :value="$extension->new_end_date" />
                    <span class="num block text-sm text-muted">+{{ (int) $extension->previous_end_date->diffInDays($extension->new_end_date) }} hari</span>
                </td>
                <td class="text-right"><x-money :amount="$extension->remaining_cost" /></td>
                <td><a href="{{ Storage::disk('public')->url($extension->payment_proof) }}" target="_blank" rel="noopener" class="text-link">Lihat bukti</a></td>
            </tr>
        @endforeach
    </x-table>
</section>
