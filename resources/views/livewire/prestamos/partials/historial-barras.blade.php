{{--
    Gráfico de historial crediticio de un cliente (comité).
    Espera: $barras = array de barras (App\Livewire\Prestamos\Show::barrasHistorial()).
    Cada barra: grupo, monto, fecha_entrega, pago_actual, total_pagos, atrasos, graves, activo, color, altura_px.
--}}
<div class="flex items-end justify-center gap-2 overflow-x-auto py-1 min-h-[6rem]">
    @forelse($barras as $b)
        <div class="flex flex-col items-center justify-end shrink-0">
            {{-- Dos números: total de atrasos (arriba) y atrasos graves >3 días (abajo) --}}
            <div class="text-[11px] leading-tight font-bold text-center mb-0.5">
                <div class="text-gray-800 dark:text-gray-200">{{ $b['atrasos'] }}</div>
                <div class="text-red-600">{{ $b['graves'] }}</div>
            </div>

            {{-- Barra: alto según monto, color según atrasos graves --}}
            <div class="relative w-6 rounded-t cursor-pointer transition-opacity hover:opacity-80"
                 style="height: {{ $b['altura_px'] }}px; background-color: {{ $b['color'] }};"
                 title="Grupo: {{ $b['grupo'] }} — ${{ number_format($b['monto'], 0) }}&#10;Entregado: {{ $b['fecha_entrega'] }}&#10;Pago {{ $b['pago_actual'] }} de {{ $b['total_pagos'] }}&#10;Atrasos: {{ $b['atrasos'] }} ({{ $b['graves'] }} graves, +3 días)">
                @if($b['activo'])
                    {{-- Indicador de crédito activo --}}
                    <span class="absolute -top-1.5 -right-1.5 h-3 w-3 rounded-full bg-blue-500 border-2 border-white shadow"
                          title="Crédito activo"></span>
                @endif
            </div>
        </div>
    @empty
        <span class="text-xs text-gray-400 self-center">Sin historial</span>
    @endforelse
</div>
