<div class="p-6 max-w-6xl mx-auto">
    {{-- Header con información del préstamo --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        @if($prestamo)
            {{-- Título y estado --}}
            <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-1">Préstamo creado con ID: {{ $prestamo->id }}</h1>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold
                        @if($prestamo->estado === 'autorizado') bg-green-100 text-green-800
                        @elseif($prestamo->estado === 'rechazado') bg-red-100 text-red-800
                        @elseif(in_array($prestamo->estado, ['pendiente', 'en_comite', 'en_curso'])) bg-yellow-100 text-yellow-800
                        @else bg-gray-100 text-gray-800 @endif">
                        Estado:
                        @if($prestamo->estado === 'en_comite')
                            en comité
                        @elseif($prestamo->estado === 'en_curso')
                            en curso
                        @else
                            {{ $prestamo->estado ?? 'N/A' }}
                        @endif
                    </span>
                </div>
            </div>

            {{-- Información del préstamo en grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Columna 1 --}}
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Producto:</p>
                        <p class="font-semibold text-gray-900">{{ ucfirst($prestamo->producto ?? 'N/A') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Fecha de entrega:</p>
                        <p class="font-semibold text-gray-900">
                            {{ $prestamo->fecha_entrega ? $prestamo->fecha_entrega->format('d/m/Y') : '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Día de pago:</p>
                        <p class="font-semibold text-gray-900">{{ ucfirst($prestamo->dia_pago ?? '—') }}</p>
                    </div>
                </div>

                {{-- Columna 2 --}}
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Plazo:</p>
                        <p class="font-semibold text-gray-900">{{ $prestamo->plazo ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Fecha primer pago:</p>
                        <p class="font-semibold text-gray-900">
                            {{ $prestamo->fecha_primer_pago ? $prestamo->fecha_primer_pago->format('d/m/Y') : '—' }}
                        </p>
                    </div>
                </div>

                {{-- Columna 3 --}}
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Periodicidad:</p>
                        <p class="font-semibold text-gray-900">{{ ucfirst($prestamo->periodicidad ?? '—') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tasa de interés:</p>
                        <p class="font-semibold text-gray-900">{{ number_format($prestamo->tasa_interes ?? 0, 1) }}%</p>
                    </div>
                </div>
            </div>

            {{-- Monto del préstamo destacado --}}
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex items-end justify-between">
                    <div class="flex items-end gap-8">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Monto solicitado:</p>
                            <p class="text-3xl font-bold text-green-600">${{ number_format($prestamo->calcularTotalSolicitado(), 2) }}</p>
                        </div>

                        @php
                            $montoTotalAutorizado = $prestamo->calcularTotalAutorizado();
                        @endphp

                        @if($montoTotalAutorizado > 0)
                            <div class="pl-8 border-l-2 border-gray-300">
                                <p class="text-sm text-gray-500 mb-1">Monto autorizado:</p>
                                <p class="text-3xl font-bold text-blue-600">${{ number_format($montoTotalAutorizado, 2) }}</p>
                            </div>
                        @endif
                    </div>

                    @if(auth()->check() && (auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Cajero') || auth()->id() === $prestamo->asesor_id) && in_array($prestamo->estado, ['en_comite', 'rechazado']))
                        <div>
                            <a href="{{ route('prestamos.edit', $prestamo->id) }}"
                               class="inline-flex items-center px-4 py-2 bg-white border border-red-300 rounded-lg text-red-600 hover:bg-red-50 transition-colors font-medium">
                                Editar préstamo
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Comentarios del comité --}}
    @if($prestamo)
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Comentarios del Comité</h3>
            <div class="text-gray-700 whitespace-pre-line">
                @if(!empty($prestamo->comentarios_comite))
                    {{ $prestamo->comentarios_comite }}
                @else
                    <p class="text-sm text-gray-500 italic">No hay comentarios del comité.</p>
                @endif
            </div>
        </div>
    @endif

    {{-- Tabla de cliente para préstamos individuales (formato similar a solicitantes) --}}
    @if($prestamo && $prestamo->producto === 'individual' && $prestamo->cliente)
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Solicitante</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nombre</th>
                            <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Historial</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Solicitado</th>
                            @if(auth()->check() && (auth()->user()->hasRole('Administrador') || auth()->id() === $prestamo->asesor_id))
                                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Autorizado</th>
                            @endif
                            <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">C.P</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">
                                    {{ trim(($prestamo->cliente->nombres ?? '') . ' ' . ($prestamo->cliente->apellido_paterno ?? '') . ' ' . ($prestamo->cliente->apellido_materno ?? '')) }}
                                </p>
                                @if($prestamo->cliente->curp)
                                    <p class="text-xs text-gray-500 mt-1">{{ $prestamo->cliente->curp }}</p>
                                @endif
                                {{-- Botón Encuesta debajo del nombre --}}
                                <div class="mt-2">
                                    <a href="{{ route('clients.show', $prestamo->cliente->id) }}"
                                       class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md transition-colors">
                                        Encuesta
                                    </a>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Gráfica de barras mini --}}
                                    <div class="flex gap-1 items-end h-12">
                                        @for($i = 1; $i <= 4; $i++)
                                            <div
                                                class="w-4 rounded-t"
                                                style="height: {{ [40, 70, 25, 85][$i-1] }}%; background-color: {{ ['#10b981', '#ef4444', '#eab308', '#10b981'][$i-1] }};"
                                                title="Préstamo {{ $i }}">
                                            </div>
                                        @endfor
                                    </div>
                                    <div class="text-sm font-bold text-gray-700">
                                        4
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="font-medium text-green-600">
                                    @php
                                        $montoSolicitadoIndividual = 0;
                                        if ($prestamo->cliente_id) {
                                            $clienteEnPivot = $prestamo->clientes->firstWhere('id', $prestamo->cliente_id);
                                            $montoSolicitadoIndividual = $clienteEnPivot->pivot->monto_solicitado ?? $prestamo->monto_total ?? 0;
                                        } else {
                                            $montoSolicitadoIndividual = $prestamo->monto_total ?? 0;
                                        }
                                    @endphp
                                    ${{ number_format($montoSolicitadoIndividual, 2) }}
                                </span>
                            </td>
                            @if(auth()->check() && (auth()->user()->hasRole('Administrador') || auth()->id() === $prestamo->asesor_id))
                                <td class="px-4 py-3 text-right">
                                    @php
                                        $montoAutorizado = null;
                                            $clienteEnPivot = $prestamo->clientes->firstWhere('id', $prestamo->cliente_id);
                                            $montoAutorizado = $clienteEnPivot->pivot->monto_autorizado ?? null;
                                            // Fallback visual: si está autorizado pero no tiene monto, mostrar solicitado
                                            if ($prestamo->estado === 'autorizado' && empty($montoAutorizado)) {
                                                $montoAutorizado = $clienteEnPivot->pivot->monto_solicitado ?? $prestamo->monto_total ?? 0;
                                            }

                                    @endphp
                                    <input
                                        type="number"
                                        step="0.01"
                                        value="{{ $montoAutorizado }}"
                                        wire:change="updateMontoAutorizadoIndividual($event.target.value)"
                                        wire:blur="updateMontoAutorizadoIndividual($event.target.value)"
                                        class="w-32 px-3 py-2 text-sm border border-gray-300 rounded-lg text-right focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 hover:border-gray-400 transition-colors {{ $prestamo->estado === 'autorizado' ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                        placeholder="0.00"
                                        @if($prestamo->estado === 'autorizado') disabled @endif
                                    />
                                </td>
                            @endif
                            <td class="px-4 py-3 text-center">
                                @if($prestamo->cliente->capacidad_pago)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Sí
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        No
                                    </span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif    {{-- Tabla de solicitantes para préstamos grupales --}}
    @if($prestamo && $prestamo->producto === 'grupal' && $prestamo->clientes && $prestamo->clientes->count())
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Solicitantes</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nombre</th>
                            <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Historial</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Sugerido</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Solicitado</th>
                            @if(auth()->check() && (auth()->user()->hasRole('Administrador') || auth()->id() === $prestamo->asesor_id))
                                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Autorizado</th>
                            @endif
                            <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">C.P</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($prestamo->clientes as $cliente)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900">
                                        {{ trim(($cliente->nombres ?? ($cliente->nombre ?? '')) . ' ' . ($cliente->apellido_paterno ?? ($cliente->apellido ?? '')) . ' ' . ($cliente->apellido_materno ?? '')) }}
                                    </p>
                                    @if($prestamo->representante_id == $cliente->id)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                            <i class="fas fa-star mr-1"></i> Representante
                                        </span>
                                    @endif
                                    {{-- Mostrar CURP debajo del nombre --}}
                                    @if($cliente->curp)
                                        <p class="text-xs text-gray-500 mt-2">{{ $cliente->curp }}</p>
                                    @endif
                                    {{-- Botón Encuesta debajo del nombre --}}
                                    <div class="mt-2">
                                        <a href="{{ route('clients.show', $cliente->id) }}"
                                           class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md transition-colors">
                                            Encuesta
                                        </a>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- Gráfica de barras mini en la tabla según bosquejo --}}
                                        <div class="flex gap-1 items-end h-12">
                                            @for($i = 1; $i <= 4; $i++)
                                                <div
                                                    class="w-4 rounded-t"
                                                    style="height: {{ [40, 70, 25, 85][$i-1] }}%; background-color: {{ ['#10b981', '#ef4444', '#eab308', '#10b981'][$i-1] }};"
                                                    title="Préstamo {{ $i }}">
                                                </div>
                                            @endfor
                                        </div>
                                        <div class="text-sm font-bold text-gray-700">
                                            4
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-medium text-blue-600">
                                        @if($cliente->pivot->monto_sugerido)
                                            ${{ number_format($cliente->pivot->monto_sugerido, 2) }}
                                        @else
                                            <span class="text-gray-400 text-sm">Pendiente</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-medium text-green-600">
                                        ${{ number_format($cliente->pivot->monto_solicitado ?? 0, 2) }}
                                    </span>
                                </td>
                                @if(auth()->check() && (auth()->user()->hasRole('Administrador') || auth()->id() === $prestamo->asesor_id))
                                    <td class="px-4 py-3 text-right">
                                        <input
                                            type="number"
                                            step="0.01"
                                            wire:model.lazy="montosAutorizados.{{ $cliente->id }}"
                                            wire:change="updateMontoAutorizado({{ $cliente->id }}, $event.target.value)"
                                            wire:blur="updateMontoAutorizado({{ $cliente->id }}, $event.target.value)"
                                            value="{{ $cliente->pivot->monto_autorizado ?? ($prestamo->estado === 'autorizado' ? ($cliente->pivot->monto_solicitado ?? '') : '') }}"
                                            class="w-32 px-3 py-2 text-sm border border-gray-300 rounded-lg text-right focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 hover:border-gray-400 transition-colors {{ $prestamo->estado === 'autorizado' ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                            placeholder="0.00"
                                            @if($prestamo->estado === 'autorizado') disabled @endif
                                        />
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-center">
                                    @if($cliente->capacidad_pago)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Sí
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            No
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Historial Crediticio OCULTO temporalmente --}}
    {{--
    @if($prestamo && $prestamo->clientes && $prestamo->clientes->count())
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Historial Crediticio
                </h3>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600">Cliente seleccionado:</span>
                    <span class="text-sm font-medium text-indigo-600" x-data="{clienteSeleccionado: null, nombreCliente: 'Ninguno'}" x-text="nombreCliente"></span>
                    <button
                        x-data="{clienteSeleccionado: null}"
                        @click="clienteSeleccionado = null; $dispatch('limpiar-seleccion')"
                        x-show="clienteSeleccionado !== null"
                        class="ml-2 text-xs text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="space-y-4">
                @foreach($prestamo->clientes as $cliente)
                    <div class="border-l-4 border-blue-500 bg-gray-50 p-4 rounded-r-lg">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-medium text-gray-900">
                                {{ trim(($cliente->nombres ?? '') . ' ' . ($cliente->apellido_paterno ?? '') . ' ' . ($cliente->apellido_materno ?? '')) }}
                            </h4>
                            <div class="text-right">
                                <div class="text-xs text-gray-500">Edad: {{ rand(25, 65) }}</div>
                                <div class="text-xs text-gray-500">{{ ['Casado', 'Soltero', 'Viudo'][ rand(0, 2)] }} - {{ rand(1, 4) }} hijos</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 mb-3">
                            <div class="flex gap-1 items-end h-12 bg-white p-2 rounded">
                                @for($i = 1; $i <= 6; $i++)
                                    <div
                                        class="w-3 rounded-t transition-all hover:opacity-75 cursor-pointer"
                                        style="height: {{ [30, 60, 45, 80, 35, 70][$i-1] }}%; background-color: {{ ['#10b981', '#ef4444', '#eab308', '#3b82f6', '#10b981', '#ef4444'][$i-1] }};"
                                        title="Préstamo {{ $i }} - {{ ['Autorizado', 'Rechazado', 'En revisión', 'En curso', 'Autorizado', 'Rechazado'][$i-1] }}">
                                    </div>
                                @endfor
                            </div>
                            <div class="text-right flex-1">
                                <div class="text-lg font-bold text-blue-600">{{ rand(3, 8) }}</div>
                                <div class="text-xs text-gray-500">préstamos</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 text-xs">
                            <div>
                                <span class="text-gray-600">Último:</span>
                                <div class="font-medium">${{ number_format(rand(5000, 15000), 0) }}</div>
                            </div>
                            <div>
                                <span class="text-gray-600">Promedio:</span>
                                <div class="font-medium">${{ number_format(rand(8000, 20000), 0) }}</div>
                            </div>
                            <div>
                                <span class="text-gray-600">Estado:</span>
                                <div class="font-medium text-green-600">{{ ['Al día', 'Vigente', 'Cumplido'][rand(0, 2)] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    --}}

    {{-- Botones de acción del comité (Administradores y asesor asignado) --}}
    @if(auth()->check() && (auth()->user()->hasRole('Administrador') || auth()->id() === $prestamo->asesor_id))
        @if($prestamo && in_array($prestamo->estado, ['pendiente', 'en_comite']))
            <div x-data="{ rejectOpen: false }" class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-gavel mr-2"></i>
                    Acciones del Comité
                </h3>

                {{-- Notas/Comentarios del comité --}}
                {{-- <div class="mb-6">
                    <label for="comentarios" class="block text-sm font-medium text-gray-700 mb-2">
                        Comentarios:
                    </label>
                    <textarea
                        id="comentarios"
                        wire:model="comentarios"
                        rows="4"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Agregue comentarios sobre la decisión del comité..."></textarea>
                </div> --}}

                {{-- Botones de Autorizar y Rechazar --}}
                @role('Administrador')
                <div class="flex flex-col sm:flex-row gap-4">
                    {{-- Botón Autorizar --}}
                    <button
                        wire:click="autorizar"
                        wire:confirm="¿Está seguro de que desea autorizar este préstamo?"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        Autorizar
                    </button>

                    {{-- Botón Rechazar --}}
                    <button
                        type="button"
                        @click="rejectOpen = !rejectOpen"
                        :aria-expanded="rejectOpen.toString()"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center">
                        <i class="fas fa-times-circle mr-2"></i>
                        Rechazar
                    </button>
                </div>
                @else
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Solo el administrador puede autorizar o rechazar préstamos.
                            </p>
                        </div>
                    </div>
                </div>
                @endrole

                {{-- Acordeón de rechazo (abre cuando el usuario presiona 'Rechazar') --}}
                <div class="mt-4">
                    <div x-show="rejectOpen" x-cloak x-transition class="bg-red-50 border border-red-200 rounded-lg p-4 mt-3">
                        <label for="motivoRechazo" class="block text-sm font-medium text-gray-700 mb-2">
                            Motivo de rechazo (requerido)
                        </label>
                        <textarea
                            id="motivoRechazo"
                            wire:model.defer="motivoRechazo"
                            rows="4"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 mb-3"
                            placeholder="Explique el motivo por el cual se rechaza el préstamo..."></textarea>

                        <div class="flex gap-3">
                            <button
                                wire:click="rechazar"
                                wire:loading.attr="disabled"
                                wire:target="rechazar"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                                Enviar rechazo
                            </button>

                            <button
                                type="button"
                                @click.prevent="rejectOpen = false; $wire.set('motivoRechazo', '');"
                                class="flex-1 bg-white border border-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($prestamo && $prestamo->estado === 'autorizado')
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-center text-sm text-gray-600 mb-4">
                    <i class="fas fa-check-circle mr-1 text-green-500"></i>
                    Este préstamo ha sido autorizado
                    @if($prestamo->autorizador)
                        por {{ $prestamo->autorizador->name }}
                    @endif
                </p>

                {{-- Botón de registro de pago/cobro --}}
                <div class="mb-6 flex justify-center">
                    <a href="{{ route('pagos.cobro-grupal', ['prestamoId' => $prestamo->id]) }}"
                       class="inline-flex items-center px-6 py-3 border border-transparent shadow-sm text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        {{ $prestamo->producto === 'grupal' ? 'Registrar Cobro Grupal' : 'Registrar Pago' }}
                    </a>
                </div>

                {{-- Botones para documentos PDF --}}
                <div class="flex flex-wrap justify-center gap-3 mt-4">
                    <a href="{{ route('prestamos.print', ['prestamo' => $prestamo->id, 'type' => 'detalle']) }}" target="_blank" rel="noopener"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Ver Detalle
                    </a>

                    <a href="{{ route('prestamos.print', ['prestamo' => $prestamo->id, 'type' => 'pagare']) }}" target="_blank" rel="noopener"
                       class="inline-flex items-center px-4 py-2 border border-blue-300 shadow-sm text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h10M7 16h10" />
                        </svg>
                        Ver Pagaré
                    </a>

                    <a href="{{ route('prestamos.print', ['prestamo' => $prestamo->id, 'type' => 'calendario']) }}" target="_blank" rel="noopener"
                       class="inline-flex items-center px-4 py-2 border border-green-300 shadow-sm text-sm font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Ver Calendario de Pagos
                    </a>
                </div>
            </div>
        @endif

        @if($prestamo && $prestamo->estado === 'rechazado')
            <div class="bg-red-50 border-2 border-red-500 shadow rounded-lg p-8">
                <div class="text-center">
                    <i class="fas fa-times-circle text-5xl text-red-600 mb-4"></i>
                    <h3 class="text-2xl font-bold text-red-800 mb-2">Préstamo Rechazado</h3>
                    <p class="text-red-700">Este préstamo ha sido rechazado y no puede ser procesado.</p>
                    @if($prestamo->motivo_rechazo)
                        <div class="mt-4 bg-red-100 border border-red-300 rounded-lg p-4">
                            <p class="text-sm font-semibold text-red-800">Motivo del rechazo:</p>
                            <p class="text-red-700 mt-1">{{ $prestamo->motivo_rechazo }}</p>
                        </div>
                    @endif
                    @if($prestamo->autorizador)
                        <p class="text-sm text-red-600 mt-4">
                            Rechazado por: <span class="font-semibold">{{ $prestamo->autorizador->name }}</span>
                        </p>
                    @endif
                </div>
            </div>
        @endif
    @else
        {{-- Mensaje para usuarios no administradores o no asignados --}}
        @if($prestamo && $prestamo->estado === 'rechazado')
            <div class="bg-red-50 border-2 border-red-500 shadow rounded-lg p-8">
                <div class="text-center">
                    <i class="fas fa-times-circle text-5xl text-red-600 mb-4"></i>
                    <h3 class="text-2xl font-bold text-red-800 mb-2">Préstamo Rechazado</h3>
                    <p class="text-red-700">Este préstamo ha sido rechazado y no puede ser procesado.</p>
                    @if($prestamo->motivo_rechazo)
                        <div class="mt-4 bg-red-100 border border-red-300 rounded-lg p-4">
                            <p class="text-sm font-semibold text-red-800">Motivo del rechazo:</p>
                            <p class="text-red-700 mt-1">{{ $prestamo->motivo_rechazo }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-center text-sm text-gray-500">
                    <i class="fas fa-lock mr-1"></i>
                    Solo los administradores y el asesor asignado pueden realizar acciones sobre este préstamo.
                </p>
            </div>
        @endif
    @endif
    {{-- Bitácora de Cambios (Timeline) --}}
    @if($prestamo && $prestamo->bitacora->count() > 0)
        <div class="bg-white shadow rounded-lg p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-history mr-2"></i>
                Bitácora del Préstamo
            </h3>
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @foreach($prestamo->bitacora as $entry)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                            @if($entry->accion === 'autorizado') bg-green-500
                                            @elseif($entry->accion === 'rechazado') bg-red-500
                                            @elseif($entry->accion === 'en_comite') bg-yellow-500
                                            @else bg-gray-500 @endif">
                                            @if($entry->accion === 'autorizado')
                                                <i class="fas fa-check text-white text-sm"></i>
                                            @elseif($entry->accion === 'rechazado')
                                                <i class="fas fa-times text-white text-sm"></i>
                                            @elseif($entry->accion === 'en_comite')
                                                <i class="fas fa-clock text-white text-sm"></i>
                                            @else
                                                <i class="fas fa-info text-white text-sm"></i>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                <span class="font-medium text-gray-900">
                                                    {{ ucfirst(str_replace('_', ' ', $entry->accion)) }}
                                                </span>
                                                por <span class="font-medium text-gray-900">{{ $entry->user->name ?? 'Sistema' }}</span>
                                            </p>
                                            @if($entry->comentarios)
                                                <p class="text-sm text-gray-600 mt-1">{{ $entry->comentarios }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                            <time datetime="{{ $entry->created_at }}">{{ $entry->created_at->format('d M Y, H:i') }}</time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>
