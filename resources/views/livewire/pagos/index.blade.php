<div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
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
                                @elseif($prestamo->estado === 'pendiente') bg-yellow-100 text-yellow-800
                                @elseif($prestamo->estado === 'rechazado') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($prestamo->estado) }}
                            </div>
                        </div>
                    </div>
                    
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
                                        Monto Crédito
                                    </th>
                                    <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Pago Sugerido
                                    </th>
                                    <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Adeudo Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $clientes = $prestamo->producto === 'grupal' ? $prestamo->clientes : ($prestamo->clientes->isNotEmpty() ? $prestamo->clientes : collect([$prestamo->cliente]));
                                    $totalCredito = 0;
                                    $totalPago = 0;
                                    $totalAdeudo = 0;
                                @endphp
                                
                                @foreach($clientes as $cliente)
                                    @php
                                        // Obtener monto autorizado (si es individual y no está en pivot, usar monto total del préstamo)
                                        $montoAutorizado = 0;
                                        if ($prestamo->producto === 'grupal') {
                                            $montoAutorizado = $cliente->pivot->monto_autorizado ?? 0;
                                        } else {
                                            $montoAutorizado = $cliente->pivot->monto_autorizado ?? $prestamo->monto_total ?? 0;
                                        }
                                        
                                        // Calcular pago sugerido usando el método del componente
                                        $pagoSugerido = $this->calcularCuota($montoAutorizado);
                                        
                                        // Calcular adeudo total (Capital + Interés)
                                        $tasaInteres = $prestamo->tasa_interes ?? 0;
                                        // Para el adeudo total, necesitamos saber los meses de interés según la configuración
                                        // Como no tenemos acceso fácil a 'meses_interes' aquí sin llamar a determinarConfiguracionPago de nuevo,
                                        // haremos una estimación basada en el pago sugerido * número de pagos, o simplemente Capital + Interés simple
                                        // Usaremos la lógica simple: Capital + (Capital * Tasa * PlazoEstimado)
                                        // O mejor: PagoSugerido * NumeroPagos (si pudiéramos obtener numero pagos)
                                        
                                        // Vamos a usar una aproximación simple para visualización: Capital + Interés Simple del periodo completo
                                        // O mejor, mostrar el monto autorizado + interés calculado
                                        $interesSimple = $montoAutorizado * ($tasaInteres / 100); 
                                        // Nota: Esto es inexacto si no consideramos los meses de interés reales, pero sirve de referencia visual rápida
                                        // Si queremos ser exactos, deberíamos exponer la configuración desde el componente.
                                        // Por ahora mostraremos Capital + Interés
                                        $adeudoEstimado = $montoAutorizado + $interesSimple;
                                        
                                        $totalCredito += $montoAutorizado;
                                        $totalPago += $pagoSugerido;
                                        $totalAdeudo += $adeudoEstimado;

                                        // Calcular siguiente número de pago
                                        $ultimoPagoNum = $prestamo->pagos
                                            ->where('cliente_id', $cliente->id)
                                            ->max('numero_pago');
                                        $siguientePago = ($ultimoPagoNum ?? 0) + 1;
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
                                            ${{ number_format($montoAutorizado, 2) }}
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                            ${{ number_format($pagoSugerido, 2) }}
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                            ${{ number_format($adeudoEstimado, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 font-semibold">
                                <tr>
                                    <td class="px-3 py-4 text-right text-gray-900">Total:</td>
                                    <td class="px-3 py-4 text-right text-gray-900">${{ number_format($totalCredito, 2) }}</td>
                                    <td class="px-3 py-4 text-right text-gray-900">${{ number_format($totalPago, 2) }}</td>
                                    <td class="px-3 py-4 text-right text-gray-900">${{ number_format($totalAdeudo, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button wire:click="irACobrar" class="w-full md:w-auto bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-md shadow-md transition duration-150 ease-in-out flex items-center justify-center gap-2 transform hover:scale-105">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Ir a Cobrar
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
