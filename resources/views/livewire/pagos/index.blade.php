<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="bg-gradient-to-r from-red-600 to-red-800 px-6 py-4">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Búsqueda de Préstamos para Cobro
            </h2>
        </div>

        <div class="p-8">
            <div class="flex flex-col md:flex-row gap-4 items-end mb-8">
                <div class="w-full md:w-1/3">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Grupo (ID Préstamo)</label>
                    <div class="relative rounded-md shadow-sm">
                        <input type="text" 
                            wire:model.live.debounce.300ms="search" 
                            id="search" 
                            class="focus:ring-red-500 focus:border-red-500 block w-full pl-4 pr-12 sm:text-lg border-gray-300 rounded-md h-12" 
                            placeholder="Ingrese ID..."
                            autofocus>
                        <div class="absolute inset-y-0 right-0 flex items-center">
                            @if($search)
                                <button wire:click="$set('search', '')" class="p-2 text-gray-400 hover:text-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="w-full md:w-auto">
                    <button wire:click="buscarPrestamo" class="w-full md:w-auto bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-md transition duration-150 ease-in-out flex items-center justify-center gap-2 h-12 border border-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Cargar
                    </button>
                </div>
            </div>

            <div wire:loading wire:target="search, buscarPrestamo" class="w-full text-center py-4">
                <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-white bg-red-500 transition ease-in-out duration-150 cursor-not-allowed">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Buscando...
                </div>
            </div>

            @if($notFound)
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                No se encontró ningún préstamo con el ID <strong>{{ $search }}</strong>.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            @if($prestamo)
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-6 animate-fade-in-up">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                                @if($prestamo->producto === 'grupal')
                                    Representante
                                @else
                                    Cliente
                                @endif
                            </label>
                            <div class="text-lg font-medium text-gray-900">
                                @if($prestamo->producto === 'grupal')
                                    {{ $prestamo->representante->nombre_completo ?? 'N/A' }}
                                @else
                                    {{ $prestamo->cliente->nombre_completo ?? 'N/A' }}
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Asesor</label>
                            <div class="text-lg font-medium text-gray-900">
                                {{ $prestamo->asesor->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Producto</label>
                            <div class="text-base text-gray-700 capitalize">
                                {{ $prestamo->producto }}
                                @if($prestamo->producto === 'grupal' && $prestamo->grupo)
                                    <span class="text-gray-500 text-sm">({{ $prestamo->grupo->nombre }})</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Estado</label>
                            <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($prestamo->estado === 'autorizado') bg-green-100 text-green-800
                                @elseif($prestamo->estado === 'liquidado') bg-blue-100 text-blue-800
                                @elseif($prestamo->estado === 'pendiente') bg-yellow-100 text-yellow-800
                                @elseif($prestamo->estado === 'rechazado') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($prestamo->estado) }}
                            </div>
                        </div>
                    </div>
                    
                    @if($prestamo->estado === 'autorizado' || $prestamo->estado === 'entregado' || $prestamo->estado === 'liquidado')
                    {{-- Tabla de clientes --}}
                    <div class="mt-8 overflow-x-auto">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            {{ $prestamo->producto === 'grupal' ? 'Clientes del Grupo' : 'Información del Cliente' }}
                        </h3>
                        <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cliente
                                    </th>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
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
                                    @if($prestamo->producto === 'grupal')
                                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center justify-center gap-1">
                                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                                <span class="text-[10px]">Todo</span>
                                            </div>
                                        </th>
                                    @endif
                                    <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Abono
                                    </th>
                                    <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Saldo Restante
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $clientes = $prestamo->producto === 'grupal' ? $prestamo->clientes : ($prestamo->clientes->isNotEmpty() ? $prestamo->clientes : collect([$prestamo->cliente]));
                                    $totalMonto = 0;
                                    $totalPendiente = 0;
                                    $totalAbono = 0;
                                    $totalSaldoRestante = 0;
                                @endphp
                                
                                @foreach($clientes as $cliente)
                                    @php
                                        // Obtener monto autorizado
                                        $montoAutorizado = 0;
                                        if ($prestamo->producto === 'grupal') {
                                            $montoAutorizado = $cliente->pivot->monto_autorizado ?? 0;
                                        } else {
                                            $montoAutorizado = $cliente->pivot->monto_autorizado ?? $prestamo->monto_total ?? 0;
                                        }
                                        
                                        // Calcular pago sugerido (Monto)
                                        $pagoSugerido = $this->calcularCuota($montoAutorizado);
                                        
                                        // Pendiente (Calculado en el componente)
                                        $pendiente = $pendientes[$cliente->id] ?? 0;
                                        
                                        // Moratorio
                                        $moratorio = $moratorios[$cliente->id] ?? 0;

                                        // Saldo Restante
                                        $saldoRestante = $saldosRestantes[$cliente->id] ?? 0;
                                        
                                        $totalMonto += $pagoSugerido;
                                        $totalPendiente += $pendiente;
                                        
                                        // Sumar abono si está definido y seleccionado
                                        $abonoActual = (float)($abonos[$cliente->id] ?? 0);
                                        if ($prestamo->producto !== 'grupal' || ($selectedClients[$cliente->id] ?? false)) {
                                             $totalAbono += $abonoActual;
                                        }
                                        
                                        $totalSaldoRestante += $saldoRestante;

                                        // Usar el número de pago calculado en el componente (Bucket Logic)
                                        $siguientePago = $siguientesPagos[$cliente->id] ?? 1;
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $cliente->nombre_completo ?? ($cliente->nombre . ' ' . $cliente->apellido_paterno) }}
                                            </div>
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900">
                                            {{ $siguientePago }}
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                            ${{ number_format($pagoSugerido, 0) }}
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                            ${{ number_format($pendiente, 0) }}
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-right text-sm text-red-600">
                                            ${{ number_format($moratorio, 0) }}
                                        </td>
                                        @if($prestamo->producto === 'grupal')
                                            <td class="px-3 py-4 whitespace-nowrap text-center">
                                                <input type="checkbox" wire:model.live="selectedClients.{{ $cliente->id }}" class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                            </td>
                                        @endif
                                        <td class="px-3 py-4 whitespace-nowrap text-right">
                                            <div class="relative rounded-md shadow-sm max-w-[120px] ml-auto">
                                                <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">$</span>
                                                </div>
                                                <input type="number" wire:model.live.debounce.500ms="abonos.{{ $cliente->id }}" class="focus:ring-red-500 focus:border-red-500 block w-full pl-6 pr-2 sm:text-sm border-gray-300 rounded-md text-right" placeholder="0.00">
                                            </div>
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                            ${{ number_format($saldoRestante, 0) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 font-semibold">
                                <tr>
                                    <td class="px-3 py-4 text-right text-gray-900">Total:</td>
                                    <td class="px-3 py-4"></td>
                                    <td class="px-3 py-4 text-right text-gray-900">${{ number_format($totalMonto, 0) }}</td>
                                    <td class="px-3 py-4 text-right text-gray-900">${{ number_format($totalPendiente, 0) }}</td>
                                    <td class="px-3 py-4 text-right text-gray-900">$0.00</td>
                                    @if($prestamo->producto === 'grupal')
                                        <td class="px-3 py-4"></td>
                                    @endif
                                    <td class="px-3 py-4 text-right text-red-600 text-lg">${{ number_format($totalAbono, 0) }}</td>
                                    <td class="px-3 py-4 text-right text-gray-900">${{ number_format($totalSaldoRestante, 0) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($prestamo->estado === 'liquidado' && $totalSaldoRestante <= 1)
                        <div class="mt-8 bg-blue-50 border-l-4 border-blue-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        Este préstamo se encuentra <strong>Liquidado</strong>. No se pueden realizar más cobros.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($prestamo->estado === 'liquidado' && $totalSaldoRestante > 1)
                        <div class="mt-8 bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <strong>Atención:</strong> El préstamo aparece como liquidado, pero aún tiene un saldo restante de <strong>${{ number_format($totalSaldoRestante, 0) }}</strong>. Puede proceder con el cobro.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-8 flex justify-end">
                            <button wire:click="irACobrar" class="w-full md:w-auto bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-md shadow-md transition duration-150 ease-in-out flex items-center justify-center gap-2 transform hover:scale-105">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                </svg>
                                Ir a Cobrar
                            </button>
                        </div>
                    @else
                        <div class="mt-8 flex justify-end">
                            <button wire:click="irACobrar" class="w-full md:w-auto bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-md shadow-md transition duration-150 ease-in-out flex items-center justify-center gap-2 transform hover:scale-105">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Ir a Cobrar
                            </button>
                        </div>
                    @endif
                    @else
                        <div class="mt-8 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Este préstamo se encuentra en estado <strong>{{ ucfirst($prestamo->estado) }}</strong>. 
                                        <br>
                                        Solo se pueden realizar cobros a préstamos que han sido <strong>Autorizados</strong> y entregados.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
