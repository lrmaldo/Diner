<div class="p-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-1">
                    Registrar {{ $prestamo->producto === 'grupal' ? 'Cobro Grupal' : 'Pago' }}
                </h1>
                <p class="text-sm text-gray-600">
                    Préstamo ID: {{ $prestamo->id }} 
                    @if($prestamo->producto === 'grupal' && $grupo)
                        - {{ $grupo->nombre }}
                    @else
                        - {{ $prestamo->producto === 'individual' ? 'Individual' : '' }}
                    @endif
                </p>
            </div>
            <a href="{{ route('prestamos.show', $prestamo->id) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>
        </div>

        {{-- Información del préstamo --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 pt-4 border-t">
            @if($prestamo->producto === 'grupal')
                <div>
                    <p class="text-sm text-gray-500">Grupo:</p>
                    <p class="font-semibold text-gray-900">{{ $grupo->nombre ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Representante:</p>
                    <p class="font-semibold text-gray-900">
                        {{ $representante ? $representante->nombre . ' ' . $representante->apellido_paterno : 'N/A' }}
                    </p>
                </div>
            @else
                <div>
                    <p class="text-sm text-gray-500">Tipo:</p>
                    <p class="font-semibold text-gray-900">Préstamo Individual</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Cliente:</p>
                    <p class="font-semibold text-gray-900">
                        {{ $representante ? $representante->nombre . ' ' . $representante->apellido_paterno : 'N/A' }}
                    </p>
                </div>
            @endif
            <div>
                <label class="block text-sm text-gray-500 mb-1">Fecha de pago:</label>
                <input type="date" 
                       wire:model.live="fechaPago" 
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Tabla de clientes --}}
        <div class="lg:col-span-2 bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ $prestamo->producto === 'grupal' ? 'Clientes del Grupo' : 'Información del Pago' }}
                </h2>
                @if($clientes->count() > 1)
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" 
                               wire:model.live="seleccionarTodos" 
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Seleccionar todos</span>
                    </label>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cliente
                            </th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pago
                            </th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Monto
                            </th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pendiente
                            </th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Moratorio
                            </th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Abono
                            </th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Adeudo Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($clientes as $cliente)
                            @php
                                $montoAutorizado = $cliente->pivot->monto_autorizado ?? 0;
                                $tasaInteres = $prestamo->tasa_interes ?? 0;
                                $plazo = $prestamo->plazo ?? 1;
                                $interesTotal = $montoAutorizado * ($tasaInteres / 100);
                                $adeudoTotal = $montoAutorizado + $interesTotal;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <input type="checkbox" 
                                           wire:model.live="clientesSeleccionados.{{ $cliente->id }}" 
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $cliente->nombre }} {{ $cliente->apellido_paterno }}
                                    </div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                    1
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-right">
                                    <input type="number" 
                                           step="0.01"
                                           min="0"
                                           wire:model.live="montosPorCliente.{{ $cliente->id }}"
                                           class="w-24 text-right rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                    ${{ number_format($montosPorCliente[$cliente->id] ?? 0, 2) }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-right text-sm text-red-600">
                                    <input type="number" 
                                           step="0.01"
                                           min="0"
                                           wire:model.live="moratoriosPorCliente.{{ $cliente->id }}"
                                           class="w-24 text-right rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-right">
                                    @if(isset($clientesSeleccionados[$cliente->id]) && $clientesSeleccionados[$cliente->id])
                                        <input type="number" 
                                               step="0.01"
                                               readonly
                                               value="{{ ($montosPorCliente[$cliente->id] ?? 0) + ($moratoriosPorCliente[$cliente->id] ?? 0) }}"
                                               class="w-24 text-right bg-green-50 border-gray-300 rounded-md shadow-sm sm:text-sm">
                                    @else
                                        <span class="text-sm text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                    ${{ number_format($adeudoTotal, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-semibold">
                        <tr>
                            <td colspan="6" class="px-3 py-4 text-right text-gray-900">
                                Total:
                            </td>
                            <td class="px-3 py-4 text-right text-lg text-gray-900">
                                ${{ number_format($totalSeleccionado, 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Nota informativa --}}
            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    Nota: El dato de la columna moratorio es solo informativo, los moratorios se tienen que cobrar por aparte.
                </p>
            </div>

            {{-- Notas --}}
            <div class="mt-4">
                <label for="notas" class="block text-sm font-medium text-gray-700 mb-1">
                    Notas adicionales (opcional)
                </label>
                <textarea id="notas"
                          wire:model="notas" 
                          rows="3" 
                          placeholder="Agregar notas sobre este cobro..."
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
            </div>
        </div>

        {{-- Panel de efectivo --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Desglose de Efectivo</h2>

            {{-- Billetes --}}
            <div class="mb-6">
                <h3 class="text-sm font-medium text-gray-700 mb-3">Billetes</h3>
                <div class="space-y-2">
                    @foreach(['1000', '500', '200', '100', '50', '20'] as $billete)
                        <div class="flex items-center gap-3">
                            <div class="w-16 h-10 bg-gradient-to-r from-green-100 to-green-200 rounded flex items-center justify-center border border-green-300">
                                <span class="text-xs font-bold text-green-800">${{ $billete }}</span>
                            </div>
                            <input type="number" 
                                   min="0"
                                   wire:model.live="desgloseBilletes.{{ $billete }}"
                                   placeholder="0"
                                   class="w-20 text-center rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <span class="text-sm text-gray-600 ml-auto">
                                ${{ number_format($billete * ($desgloseBilletes[$billete] ?? 0), 2) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Monedas --}}
            <div class="mb-6">
                <h3 class="text-sm font-medium text-gray-700 mb-3">Monedas</h3>
                <div class="space-y-2">
                    @foreach(['20', '10', '5', '2', '1', '0.5'] as $moneda)
                        <div class="flex items-center gap-3">
                            <div class="w-16 h-10 bg-gradient-to-r from-yellow-100 to-yellow-200 rounded-full flex items-center justify-center border border-yellow-300">
                                <span class="text-xs font-bold text-yellow-800">${{ $moneda }}</span>
                            </div>
                            <input type="number" 
                                   min="0"
                                   wire:model.live="desgloseMonedas.{{ $moneda }}"
                                   placeholder="0"
                                   class="w-20 text-center rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <span class="text-sm text-gray-600 ml-auto">
                                ${{ number_format($moneda * ($desgloseMonedas[$moneda] ?? 0), 2) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Resumen --}}
            <div class="border-t pt-4 space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Total a cobrar:</span>
                    <span class="font-semibold text-gray-900">${{ number_format($totalSeleccionado, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Efectivo recibido:</span>
                    <span class="font-semibold text-gray-900">${{ number_format($totalEfectivo, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-lg font-bold border-t pt-3
                    {{ $diferencia < 0 ? 'text-red-600' : ($diferencia > 0 ? 'text-green-600' : 'text-gray-900') }}">
                    <span>{{ $diferencia < 0 ? 'Faltan:' : ($diferencia > 0 ? 'Cambio:' : 'Diferencia:') }}</span>
                    <span>${{ number_format(abs($diferencia), 2) }}</span>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="mt-6 space-y-2">
                <button type="button"
                        wire:click="registrarPagos"
                        wire:loading.attr="disabled"
                        class="w-full inline-flex items-center justify-center px-4 py-3 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span wire:loading.remove wire:target="registrarPagos">Aceptar y Registrar Pagos</span>
                    <span wire:loading wire:target="registrarPagos">Procesando...</span>
                </button>
                <a href="{{ route('prestamos.show', $prestamo->id) }}"
                   class="w-full inline-flex items-center justify-center px-4 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancelar
                </a>
            </div>
        </div>
    </div>

    {{-- Loading overlay --}}
    <div wire:loading wire:target="registrarPagos" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center gap-4">
            <svg class="w-6 h-6 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-700">Procesando pagos...</span>
        </div>
    </div>
</div>
