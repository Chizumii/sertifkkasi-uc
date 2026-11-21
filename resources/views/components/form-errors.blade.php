{{-- Ringkasan error validasi di atas form. Detail tiap error tampil di dekat field masing-masing. --}}
@if ($errors->any())
    <x-alert type="error" {{ $attributes }}>
        <p class="font-medium">Data belum bisa disimpan. Perbaiki {{ $errors->count() }} isian berikut:</p>
        <ul class="mt-1 list-disc pl-5">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif
