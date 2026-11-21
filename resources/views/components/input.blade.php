{{-- Field teks: label di atas, input, lalu baris bantuan yang diganti pesan error bila validasi gagal. --}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null, 'required' => false])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $describedBy = $id.'-help';
    $currentValue = $type === 'password' ? null : old($name, $value);
@endphp

<div {{ $attributes->only('class')->class('grid gap-1.5') }}>
    <label for="{{ $id }}" class="text-sm font-medium text-ink-2">
        {{ $label }}
        @unless ($required)
            <span class="font-normal text-muted">(opsional)</span>
        @endunless
    </label>
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if (! is_null($currentValue)) value="{{ $currentValue }}" @endif
        @required($required)
        @if ($required) aria-required="true" @endif
        aria-describedby="{{ $describedBy }}"
        @if ($hasError) aria-invalid="true" @endif
        {{ $attributes->except(['class', 'id'])->class('field') }}
    >
    <p id="{{ $describedBy }}" class="min-h-lh text-sm {{ $hasError ? 'text-error' : 'text-muted' }}">
        @error($name)
            {{ $message }}
        @else
            {{ $help }}
        @enderror
    </p>
</div>
