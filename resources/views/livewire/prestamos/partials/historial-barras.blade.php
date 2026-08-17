{{--
    Gráfico de historial crediticio de un cliente (comité).
    Espera: $barras = array de barras (App\Livewire\Prestamos\Show::barrasHistorial()).
    Cada barra: grupo, monto, fecha_entrega, pago_actual, total_pagos, atrasos, graves, activo, color, altura_px.
--}}
<div class="flex items-end justify-start gap-1 overflow-x-auto py-1 min-h-[4.5rem]">
    @forelse($barras as $b)
        <div class="flex flex-col items-center justify-end shrink-0">
            {{-- Dos números: total de atrasos (arriba) y graves >3 días (abajo). Sólo si hay atrasos. --}}
            <div class="h-6 flex flex-col justify-end text-[10px] leading-none font-bold text-center mb-0.5">
                @if($b['atrasos'] > 0)
                    <div class="text-gray-800 dark:text-gray-200">{{ $b['atrasos'] }}</div>
                    <div class="text-red-600">{{ $b['graves'] }}</div>
                @endif
            </div>

            {{-- Barra: alto según monto, color según número de atrasos --}}
            <div class="relative w-3 rounded-t cursor-pointer transition-opacity hover:opacity-80"
                 style="height: {{ $b['altura_px'] }}px; background-color: {{ $b['color'] }};"
                 title="Grupo: {{ $b['grupo'] }} — ${{ number_format($b['monto'], 0) }}&#10;Entregado: {{ $b['fecha_entrega'] }}&#10;Pago {{ $b['pago_actual'] }} de {{ $b['total_pagos'] }}&#10;Atrasos: {{ $b['atrasos'] }} ({{ $b['graves'] }} graves, +3 días)">
                @if($b['activo'])
                    {{-- Indicador de crédito activo --}}
                    <span class="absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full bg-blue-600 border-2 border-white shadow"
                          title="Crédito activo"></span>
                @endif
            </div>
        </div>
    @empty
        <span class="text-xs text-gray-400 self-center">Sin historial</span>
    @endforelse
</div>
