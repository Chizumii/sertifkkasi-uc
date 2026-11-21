{{-- Kartu plate bergaris tinta 2px dengan bayangan offset tegas. Jangan menaruh card di dalam card. --}}
@props(['title' => null, 'description' => null])

<section {{ $attributes->class('border-2 border-ink bg-surface p-5 shadow-card sm:p-6') }}>
    @if ($title)
        <header class="mb-4 grid gap-1">
            <h2 class="text-lg">{{ $title }}</h2>
            @if ($description)
                <p class="text-sm text-muted">{{ $description }}</p>
            @endif
        </header>
    @endif
    {{ $slot }}
</section>
