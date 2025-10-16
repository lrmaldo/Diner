<div class="p-6 max-w-6xl mx-auto">
    {{-- Header compacto según bosquejo --}}
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        @if($prestamo)
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">PRE-{{ str_pad($prestamo->id, 4, '0', STR_PAD_LEFT) }}</h1>
                    <p class="text-sm text-gray-600">{{ ucfirst($prestamo->producto ?? 'N/A') }} - ${{ number_format($prestamo->monto_total ?? 0, 2) }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($prestamo->estado === 'autorizado') bg-green-100 text-green-800
                        @elseif($prestamo->estado === 'rechazado') bg-red-100 text-red-800
                        @elseif(in_array($prestamo->estado, ['pendiente', 'en_revision'])) bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-800 @endif">
                        @if($prestamo->estado === 'en_revision')
                            En revisión
                        @else
                            {{ ucfirst($prestamo->estado ?? 'N/A') }}
                        @endif
                    </span>
                </div>
            </div>
        @endif
    </div>

    {{-- Información del cliente para préstamos individuales --}}
    @if($prestamo && $prestamo->producto === 'individual' && $prestamo->cliente)
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Cliente</h3>
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-900">
                        {{ trim(($prestamo->cliente->nombres ?? '') . ' ' . ($prestamo->cliente->apellido_paterno ?? '') . ' ' . ($prestamo->cliente->apellido_materno ?? '')) }}
                    </p>
                    @if($prestamo->cliente->email)
                        <p class="text-sm text-gray-600 mt-1">{{ $prestamo->cliente->email }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Monto solicitado</p>
                    <p class="text-xl font-bold text-green-600">${{ number_format($prestamo->monto_total ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Tabla de solicitantes según bosquejo --}}
    {{-- Solo mostrar para préstamos grupales que tienen clientes en la tabla pivot --}}
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

    {{-- Botones de acción del comité (SOLO ADMINISTRADORES) --}}
    @if(auth()->user()->isAdmin())
        @if($prestamo && in_array($prestamo->estado, ['pendiente', 'en_revision']))
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-gavel mr-2"></i>
                    Acciones del Comité
                </h3>

                {{-- Notas/Comentarios del comité --}}
                <div class="mb-6">
                    <label for="comentarios" class="block text-sm font-medium text-gray-700 mb-2">
                        Comentarios:
                    </label>
                    <textarea
                        id="comentarios"
                        wire:model="comentarios"
                        rows="4"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Agregue comentarios sobre la decisión del comité..."></textarea>
                </div>

                {{-- Botones de Autorizar y Rechazar --}}
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
                        wire:click="rechazar"
                        wire:confirm="¿Está seguro de que desea rechazar este préstamo?"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center">
                        <i class="fas fa-times-circle mr-2"></i>
                        Rechazar
                    </button>
                </div>
            </div>
        @endif

        @if($prestamo && $prestamo->estado === 'autorizado')
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-center text-sm text-gray-600">
                    <i class="fas fa-check-circle mr-1 text-green-500"></i>
                    Este préstamo ha sido autorizado
                    @if($prestamo->autorizador)
                        por {{ $prestamo->autorizador->name }}
                    @endif
                </p>
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
        {{-- Mensaje para usuarios no administradores --}}
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
                    Solo los administradores pueden realizar acciones sobre este préstamo.
                </p>
            </div>
        @endif
    @endif
</div>
