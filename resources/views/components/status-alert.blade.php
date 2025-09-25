@props([
    'type' => 'success', // success | error | info | warning
    'message' => null,
    'timeout' => 5000,
])

@if($message)
    <div
        x-data="{ show: true }"
        x-init="if ({{ (int)$timeout }} > 0) { setTimeout(() => show = false, {{ (int)$timeout }}); }"
        x-show="show"
        x-transition.opacity.duration.200ms
        class="mb-4 p-3 rounded border flex items-start justify-between {{ [
            'success' => 'bg-green-50 border-green-300 text-green-700',
            'error' => 'bg-red-50 border-red-300 text-red-700',
            'info' => 'bg-blue-50 border-blue-300 text-blue-700',
            'warning' => 'bg-yellow-50 border-yellow-300 text-yellow-800',
        ][$type] ?? 'bg-gray-50 border-gray-300 text-gray-700' }}"
    >
        <div class="text-sm font-medium flex-1">{{ $message }}</div>
        <button type="button" @click="show = false" class="ml-4 text-xs uppercase tracking-wide hover:underline">Cerrar</button>
    </div>
@endif
