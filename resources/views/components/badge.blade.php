{{-- Badge status. Menerima enum (memakai label()) atau string nilai enum. --}}
@props(['status'])

@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $label = $status instanceof \BackedEnum && method_exists($status, 'label') ? $status->label() : ucfirst($value);
    $tones = [
        'approved' => 'bg-surface text-ink ring-1 ring-ink',
        'available' => 'bg-surface text-ink ring-1 ring-ink',
        'pending' => 'bg-accent-soft text-accent-deep ring-1 ring-accent',
        'rejected' => 'bg-error-soft text-error ring-1 ring-error',
        'used' => 'bg-ink text-paper',
        'daily' => 'bg-paper-2 text-ink-2 ring-1 ring-rule',
        'monthly' => 'bg-paper-2 text-ink-2 ring-1 ring-rule',
        'scheduled' => 'bg-accent-soft text-accent-deep ring-1 ring-accent',
        'active' => 'bg-ink text-paper',
        'completed' => 'bg-paper-2 text-muted ring-1 ring-rule',    ];
@endphp

<span {{ $attributes->class(['inline-flex items-center px-2 py-0.5 text-sm font-medium whitespace-nowrap', $tones[$value] ?? 'bg-paper-2 text-ink-2']) }}>{{ $label }}</span>
