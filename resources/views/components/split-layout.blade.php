{{--
    Tata letak dua kolom untuk mengurangi scroll: aksi (filter, form, tombol) di kiri, data di kanan.
    Di layar lebar kolom kiri menempel (sticky) di bawah bilah atas dan bisa di-scroll sendiri bila lebih tinggi dari layar;
    di layar kecil aksi tampil di atas data.
    Kedua kolom adalah container query (@container), jadi grid di dalamnya memakai varian @sm/@lg/... sesuai lebar kolom,
    bukan lebar layar, agar isi tidak meluber atau bertumpuk.
    Slot "aside" berisi aksi, slot utama berisi data. asideLabel menamai kolom aksi untuk pembaca layar.
--}}
@props(['asideLabel' => 'Aksi', 'asideWidth' => '18rem'])

<div {{ $attributes->class('split-layout grid items-start gap-8 lg:gap-10') }} style="--split-aside: {{ $asideWidth }}">
    <aside aria-label="{{ $asideLabel }}" class="split-aside @container grid min-w-0 content-start gap-6">
        {{ $aside }}
    </aside>
    <div class="@container grid min-w-0 content-start gap-6">
        {{ $slot }}
    </div>
</div>
