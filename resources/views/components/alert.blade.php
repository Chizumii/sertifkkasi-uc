{{-- Pesan status halaman: success, error, atau info. Dibacakan pembaca layar lewat aria-live. --}}
@props(['type' => 'info'])

@php
    $tones = [
        'success' => 'bg-surface text-ink border-l-4 border-ink',
        'error' => 'bg-error-soft text-ink border-l-4 border-error',
        'info' => 'bg-paper-2 text-ink border-l-4 border-rule',
    ];
@endphp

<div
    role="{{ $type === 'error' ? 'alert' : 'status' }}"
    aria-live="{{ $type === 'error' ? 'assertive' : 'polite' }}"
    {{ $attributes->class(['px-4 py-3', $tones[$type] ?? $tones['info']]) }}
>
    {{ $slot }}
</div>
