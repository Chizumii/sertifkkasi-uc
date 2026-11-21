{{-- Judul halaman, satu kalimat penjelas, dan aksi halaman di sisi kanan (slot). --}}
@props(['title', 'description' => null])

<header {{ $attributes->class('flex flex-wrap items-end justify-between gap-4') }}>
    <div class="grid min-w-0 gap-1">
        <h1 class="text-2xl sm:text-3xl">{{ $title }}</h1>
        @if ($description)
            <p class="max-w-prose text-muted">{{ $description }}</p>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="flex flex-wrap gap-3">{{ $slot }}</div>
    @endif
</header>
