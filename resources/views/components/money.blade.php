{{-- Format Rupiah tanpa desimal, misalnya Rp 1.250.000. --}}
@props(['amount'])

<span {{ $attributes->class('num whitespace-nowrap') }}>Rp&nbsp;{{ number_format((float) $amount, 0, ',', '.') }}</span>
