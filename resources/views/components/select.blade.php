{{-- Pilihan native. Isi lewat $options (nilai => teks) atau slot <option> sendiri. --}}
@props(['name', 'label', 'options' => [], 'selected' => null, 'placeholder' => null, 'help' => null, 'required' => false])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $describedBy = $id.'-help';
    $currentValue = (string) old($name, $selected instanceof \BackedEnum ? $selected->value : $selected);
@endphp

<div {{ $attributes->only('class')->class('grid gap-1.5') }}>
    <label for="{{ $id }}" class="text-sm font-medium text-ink-2">
        {{ $label }}
        @unless ($required)
            <span class="font-normal text-muted">(opsional)</span>
        @endunless
    </label>
    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @required($required)
        @if ($required) aria-required="true" @endif
        aria-describedby="{{ $describedBy }}"
        @if ($hasError) aria-invalid="true" @endif
        {{ $attributes->except(['class', 'id'])->class('field') }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($currentValue === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
        {{ $slot }}
    </select>
    <p id="{{ $describedBy }}" class="min-h-lh text-sm {{ $hasError ? 'text-error' : 'text-muted' }}">
        @error($name)
            {{ $message }}
        @else
            {{ $help }}
        @enderror
    </p>
</div>
