{{-- Unggah gambar (jpg/png, maks 2 MB) dengan pratinjau. Pratinjau diisi oleh resources/js/app.js. --}}
@props(['name', 'label', 'help' => 'Format JPG atau PNG, maksimal 2 MB.', 'required' => false, 'disabled' => false])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $describedBy = $id.'-help';
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
        type="file"
        accept="image/jpeg,image/png"
        data-image-preview="{{ $id }}-preview"
        @required($required)
        @disabled($disabled)
        @if ($required) aria-required="true" @endif
        aria-describedby="{{ $describedBy }}"
        @if ($hasError) aria-invalid="true" @endif
        class="field file:mr-3 file:rounded-input file:border-0 file:bg-paper-3 file:px-3 file:py-1 file:text-ink"
    >
    <p id="{{ $describedBy }}" class="min-h-lh text-sm {{ $hasError ? 'text-error' : 'text-muted' }}">
        @error($name)
            {{ $message }}
        @else
            {{ $help }}
        @enderror
    </p>
    <div id="{{ $id }}-preview" data-preview-alt="Pratinjau {{ strtolower($label) }}" class="w-fit max-w-full" hidden></div>
</div>
