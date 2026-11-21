{{-- Tanggal berbahasa Indonesia, misalnya 21 November 2026. Menerima Carbon atau string Y-m-d. --}}
@props(['value', 'format' => 'j F Y'])

@if ($value)
    <time datetime="{{ \Illuminate\Support\Carbon::parse($value)->toDateString() }}" {{ $attributes->class('num whitespace-nowrap') }}>{{ \Illuminate\Support\Carbon::parse($value)->locale('id')->translatedFormat($format) }}</time>
@else
    <span {{ $attributes }}>-</span>
@endif
