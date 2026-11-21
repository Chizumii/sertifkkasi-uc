{{-- Tombol plastik. Varian: primary (satu per halaman), secondary, danger. Menjadi tautan bila href diisi. --}}
@props(['variant' => 'primary', 'type' => 'submit', 'href' => null])

@php
    $classes = 'btn btn-'.(in_array($variant, ['primary', 'secondary', 'danger'], true) ? $variant : 'primary');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
