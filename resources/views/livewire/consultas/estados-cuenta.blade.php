<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <h2 class="text-lg font-medium mb-6">Estados de cuenta</h2>

            {{-- Search Form --}}
            <div class="space-y-4 max-w-2xl mb-8">
                <!-- Busqueda por Grupo -->
                <div class="flex items-center gap-4">
                    <flux:label for="grupo" class="w-40 text-right">Numero de grupo</flux:label>
                    <div class="flex-1">
                        <flux:input type="text" id="grupo" wire:model="grupo" />
                    </div>
                    <flux:button wire:click="buscarPorGrupo">
                        Buscar
                    </flux:button>
                </div>

                <!-- Busqueda por Nombre -->
                <div class="flex items-center gap-4">
                    <flux:label for="nombre" class="w-40 text-right">Nombre</flux:label>
                    <div class="flex-1">
                        <flux:input type="text" id="nombre" wire:model="nombre" />
                    </div>
                    <flux:button wire:click="buscarPorNombre">
                        Buscar
                    </flux:button>
                </div>
            </div>

            {{-- No Results Message --}}
            @if($noResults)
                <div class="rounded-md bg-red-50 p-4 mb-6 dark:bg-red-900/20">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">No se encontraron resultados</h3>
                            <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                <p>No se encontró ningún registro con los criterios de búsqueda proporcionados.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Results Table --}}
            @if(count($results) > 0 && !$selectedClient)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-zinc-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Curp</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Municipio</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Ver</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($results as $client)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $client->curp }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $client->nombres }} {{ $client->apellido_paterno }} {{ $client->apellido_materno }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $client->municipio }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $client->estado }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click="selectClient({{ $client->id }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 uppercase font-bold">VER</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Detailed View --}}
            @if($selectedClient)
                <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-8">
                    <div class="flex justify-between items-start mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Detalle del Cliente</h3>
                        <button wire:click="resetSearch" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            &larr; Volver a resultados
                        </button>
                    </div>

                    {{-- Client Info Grid --}}
                    <div class="bg-gray-50 dark:bg-zinc-700/50 p-4 rounded-lg mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                            <div class="flex">
                                <span class="w-32 font-medium text-gray-500 dark:text-gray-400 uppercase text-sm">Nombre:</span>
                                <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $selectedClient->nombres }} {{ $selectedClient->apellido_paterno }} {{ $selectedClient->apellido_materno }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-32 font-medium text-gray-500 dark:text-gray-400 uppercase text-sm">Celular:</span>
                                <span class="text-gray-900 dark:text-gray-100">{{ $selectedClient->telefonos->first()->numero ?? 'N/A' }}</span>
                            </div>

                            <div class="flex">
                                <span class="w-32 font-medium text-gray-500 dark:text-gray-400 uppercase text-sm">Curp:</span>
                                <span class="text-gray-900 dark:text-gray-100">{{ $selectedClient->curp }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-32 font-medium text-gray-500 dark:text-gray-400 uppercase text-sm">Casa:</span>
                                <span class="text-gray-900 dark:text-gray-100">N/A</span> {{-- Assuming Casa phone is not distinct or stored differently --}}
                            </div>

                            <div class="flex">
                                <span class="w-32 font-medium text-gray-500 dark:text-gray-400 uppercase text-sm">Municipio:</span>
                                <span class="text-gray-900 dark:text-gray-100">{{ $selectedClient->municipio }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-32 font-medium text-gray-500 dark:text-gray-400 uppercase text-sm">Fecha de Nacimiento:</span>
                                <span class="text-gray-900 dark:text-gray-100">N/A</span> {{-- Need to extract from CURP or add field --}}
                            </div>

                            <div class="flex">
                                <span class="w-32 font-medium text-gray-500 dark:text-gray-400 uppercase text-sm">Localidad:</span>
                                <span class="text-gray-900 dark:text-gray-100">{{ $selectedClient->municipio }}</span> {{-- Assuming Localidad is same as Municipio for now --}}
                            </div>
                            <div class="hidden md:block"></div>

                            <div class="flex">
                                <span class="w-32 font-medium text-gray-500 dark:text-gray-400 uppercase text-sm">Direccion:</span>
                                <span class="text-gray-900 dark:text-gray-100">{{ $selectedClient->calle_numero }}, {{ $selectedClient->colonia }}</span>
                            </div>
                            <div class="hidden md:block"></div>

                            <div class="flex">
                                <span class="w-32 font-medium text-gray-500 dark:text-gray-400 uppercase text-sm">Sexo:</span>
                                <span class="text-gray-900 dark:text-gray-100">N/A</span> {{-- Need to extract from CURP --}}
                            </div>
                        </div>
                    </div>

                    {{-- Loans Table --}}
                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-zinc-700">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-600">Numero</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-600">Grupo</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-600">Producto</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-600">Periodo de Pago</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-600">Plazo</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-600">Tasa</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-600">Monto</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-600">Fecha de Entrega</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-600">Vigente</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Vencido</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($clientLoans as $index => $loan)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-600">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-blue-600 dark:text-blue-400 border-r border-gray-200 dark:border-gray-600 font-bold">
                                            <button wire:click="selectLoan({{ $loan->id }})" class="hover:underline">{{ $loan->id }}</button>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-600 uppercase">{{ $loan->producto }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-600">{{ $loan->periodo_pago }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-600">{{ $loan->plazo }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-600">{{ $loan->tasa_interes }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-600">{{ number_format($loan->monto_total, 2) }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-600">{{ $loan->fecha_entrega ? $loan->fecha_entrega->format('d-m-y') : 'N/A' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-600"></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Selected Loan Detail (Estado de Cuenta) --}}
            @if($selectedLoan)
                <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-8">
                    <div class="flex justify-between items-start mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Estado de Cuenta - Crédito #{{ $selectedLoan->id }}</h3>
                        <button wire:click="resetLoan" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            &larr; Volver a lista de créditos
                        </button>
                    </div>

                    <div class="bg-white dark:bg-zinc-800 shadow overflow-hidden sm:rounded-lg mb-6">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Detalles del Préstamo</h3>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700">
                            <dl>
                                <div class="bg-gray-50 dark:bg-zinc-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Monto Total</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0 sm:col-span-2">${{ number_format($selectedLoan->monto_total, 2) }}</dd>
                                </div>
                                <div class="bg-white dark:bg-zinc-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Entrega</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0 sm:col-span-2">{{ $selectedLoan->fecha_entrega ? $selectedLoan->fecha_entrega->format('d/m/Y') : 'N/A' }}</dd>
                                </div>
                                <div class="bg-gray-50 dark:bg-zinc-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tasa de Interés</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0 sm:col-span-2">{{ $selectedLoan->tasa_interes }}%</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Historial de Pagos</h4>
                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-zinc-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Monto Pagado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Capital</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Interés</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Saldo Restante</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($selectedLoan->pagos as $pago)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">${{ number_format($pago->monto, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">${{ number_format($pago->capital_pagado, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">${{ number_format($pago->interes_pagado, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">${{ number_format($pago->saldo_nuevo, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">No hay pagos registrados para este préstamo.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
