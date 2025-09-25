<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>

    {{-- Global toast container included at layout level to avoid Livewire multiple root issues --}}
    @include('components.toast')
</x-layouts.app.sidebar>
