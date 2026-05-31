<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white border border-gray-300 max-w-2xl mx-auto">
        <div class="bg-red-600 text-white font-bold text-center py-2 flex justify-between items-center px-4">
            <div class="flex-grow text-center text-lg">Parámetros de consulta</div>
            <svg class="h-5 w-5 text-white opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
        <div class="p-6 border-t border-gray-300">
            @if (session()->has('message'))
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded border border-green-200 text-center">
                    {{ session('message') }}
                </div>
            @endif
            
            <div class="p-0 space-y-4">
                <select wire:model="parametro" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm text-center">
                    @foreach($opciones as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                
                <div class="flex justify-center pt-4">
                    <button type="button" wire:click="generar" class="inline-flex justify-center py-2 px-8 border border-red-700 shadow-sm text-sm font-bold rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Generar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if($showReport)
    {{-- Paletas de Información --}}
    <div class="mt-8 flex flex-row flex-wrap xl:flex-nowrap justify-between items-stretch gap-4 p-4 bg-gray-50 rounded-xl border border-gray-300 w-full">
        
        {{-- Paleta: Clientes (Rosa) --}}
        <div class="flex-1 bg-pink-500 rounded-lg text-white p-4 shadow min-w-32">
            <h3 class="font-bold text-center border-b border-pink-400 pb-2 mb-2">Clientes</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['actual'] }}:</span>
                    <button type="button" wire:click="openClientesModal('al_dia')" class="font-bold underline decoration-white/50 hover:decoration-white">{{ number_format($this->datosClientes['al_dia']) }}</button>
                </div>
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['mes1'] }}:</span>
                    <button type="button" wire:click="openClientesModal('mes1')" class="font-bold underline decoration-white/50 hover:decoration-white">{{ number_format($this->datosClientes['mes1']) }}</button>
                </div>
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['mes2'] }}:</span>
                    <button type="button" wire:click="openClientesModal('mes2')" class="font-bold underline decoration-white/50 hover:decoration-white">{{ number_format($this->datosClientes['mes2']) }}</button>
                </div>
            </div>
        </div>

        {{-- Paleta: Colocación (Rojo) --}}
        <div class="flex-1 bg-red-600 rounded-lg text-white p-4 shadow min-w-32">
            <h3 class="font-bold text-center border-b border-red-500 pb-2 mb-2">Colocación</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['actual'] }}:</span>
                    <span class="font-bold">${{ number_format($this->datosColocacion['al_dia'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['mes1'] }}:</span>
                    <span class="font-bold">${{ number_format($this->datosColocacion['mes1'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['mes2'] }}:</span>
                    <span class="font-bold">${{ number_format($this->datosColocacion['mes2'], 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Paleta: Fidelización (Amarillo Verde) --}}
        <div class="flex-1 bg-yellow-400 rounded-lg text-white p-4 shadow min-w-32">
            <h3 class="font-bold text-center border-b border-yellow-300 pb-2 mb-2 text-shadow-sm">Fidelización</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['actual'] }}:</span>
                    <span class="font-bold">{{ number_format($this->datosFidelizacion['al_dia'] ?? 0, 2) }}%</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['mes1'] }}:</span>
                    <span class="font-bold">{{ number_format($this->datosFidelizacion['mes1'] ?? 0, 2) }}%</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['mes2'] }}:</span>
                    <span class="font-bold">{{ number_format($this->datosFidelizacion['mes2'] ?? 0, 2) }}%</span>
                </div>
            </div>
        </div>

        {{-- Paleta: Exigible (Verde) --}}
        <div class="flex-1 bg-green-500 rounded-lg text-white p-4 shadow min-w-32">
            <h3 class="font-bold text-center border-b border-green-400 pb-2 mb-2">Exigible</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['actual'] }}:</span>
                    <span class="font-bold">{{ number_format($this->datosExigible['al_dia'], 2) }}%</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['mes1'] }}:</span>
                    <span class="font-bold">{{ number_format($this->datosExigible['mes1'], 2) }}%</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['mes2'] }}:</span>
                    <span class="font-bold">{{ number_format($this->datosExigible['mes2'], 2) }}%</span>
                </div>
            </div>
        </div>

        {{-- Paleta: Monto Activo (Celeste) --}}
        <div class="flex-1 bg-cyan-400 rounded-lg text-white p-4 shadow flex flex-col justify-center min-w-32">
            <h3 class="font-bold text-center border-b border-cyan-300 pb-2 mb-4">Monto activo:</h3>
            <div class="text-center text-xl font-bold">
                $ {{ number_format($this->datosMontoActivo, 2) }}
            </div>
        </div>
    </div>

    {{-- Tabla de Saldo de Cartera (Atrasos) --}}
    <div class="mt-12 overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-300 text-sm">
            <thead class="bg-red-600 text-white">
                <tr>
                    <th class="py-2 px-2 border border-gray-300 text-left font-bold w-48 text-xs">Asesor</th>
                    <th class="py-2 px-2 border border-gray-300 text-left font-bold text-xs">C. vigente</th>
                    <th class="py-2 px-2 border border-gray-300 text-left font-bold text-xs">Cv de 1 a 7 dias</th>
                    <th class="py-2 px-2 border border-gray-300 text-left font-bold text-xs">Cv de 8 a 30 dias</th>
                    <th class="py-2 px-2 border border-gray-300 text-left font-bold text-xs">Cv de 31 a 90 dias</th>
                    <th class="py-2 px-2 border border-gray-300 text-left font-bold text-xs">Cv de 91 a 180</th>
                    <th class="py-2 px-2 border border-gray-300 text-left font-bold text-xs">Cv de 181 a 365</th>
                    <th class="py-2 px-2 border border-gray-300 text-left font-bold text-xs">Cv de mas de 365 dias</th>
                    <th class="py-2 px-2 border border-gray-300 text-left font-bold text-xs">Cv total</th>
                    <th class="py-2 px-2 border border-gray-300 text-center font-bold text-xs">créditos</th>
                    <th class="py-2 px-2 border border-gray-300 text-center font-bold text-xs">clientes</th>
                    <th class="py-2 px-2 border border-gray-300 text-left font-bold text-xs">Saldo total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($this->datosCarteraPorAsesor as $fila)
                    <tr wire:key="asesor-{{ md5($fila['asesor']) }}" class="hover:bg-gray-50 border-b border-gray-300 text-xs">
                        <td class="py-2 px-2 border-r border-gray-300 font-medium">{{ $fila['asesor'] }}</td>
                        
                        @foreach(['c_vigente', 'cv_1_7', 'cv_8_30', 'cv_31_90', 'cv_91_180', 'cv_181_365', 'cv_mas_365', 'cv_total'] as $col)
                            <td class="border-r border-gray-300 p-1 align-top w-28">
                                <div class="flex flex-col space-y-1">
                                    <span>{{ number_format($fila[$col]['saldo']) }}</span>
                                    @if($col !== 'cv_total')
                                        <button type="button" wire:click="openClientesBucketModal('{{ $col }}')" class="text-left underline decoration-gray-500 hover:decoration-red-600">
                                            {{ $fila[$col]['clientes'] }}
                                        </button>
                                    @else
                                        <span>{{ $fila[$col]['clientes'] }}</span>
                                    @endif
                                    <span>{{ $fila[$col]['porcentaje'] }}%</span>
                                </div>
                            </td>
                        @endforeach
                        
                        <td class="border-r border-gray-300 text-center p-2">{{ number_format($fila['creditos']) }}</td>
                        <td class="border-r border-gray-300 text-center p-2">{{ number_format($fila['clientes']) }}</td>
                        <td class="p-2 font-medium">{{ number_format($fila['saldo_total']) }}</td>
                    </tr>
                @endforeach
                
                {{-- Fila de Totales --}}
                @php $totales = $this->datosCarteraTotales; @endphp
                <tr class="bg-red-600 text-white font-bold text-xs">
                    <td class="py-2 px-2 border border-red-700">Totales</td>
                    
                    @foreach(['c_vigente', 'cv_1_7', 'cv_8_30', 'cv_31_90', 'cv_91_180', 'cv_181_365', 'cv_mas_365', 'cv_total'] as $col)
                        <td class="border border-red-700 p-1 align-top w-28">
                            <div class="flex flex-col space-y-1">
                                <span>{{ number_format($totales[$col]['saldo']) }}</span>
                                @if($col !== 'cv_total')
                                    <button type="button" wire:click="openClientesBucketModal('{{ $col }}')" class="text-left underline decoration-white/70 hover:decoration-white">
                                        {{ $totales[$col]['clientes'] }}
                                    </button>
                                @else
                                    <span>{{ $totales[$col]['clientes'] }}</span>
                                @endif
                                <span>{{ $totales[$col]['porcentaje'] }}%</span>
                            </div>
                        </td>
                    @endforeach
                    
                    <td class="border border-red-700 text-center p-2">{{ number_format($totales['creditos']) }}</td>
                    <td class="border border-red-700 text-center p-2">{{ number_format($totales['clientes']) }}</td>
                    <td class="border border-red-700 p-2 font-medium">{{ number_format($totales['saldo_total']) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    @if($showClientesModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" wire:click="closeClientesModal"></div>
            <div class="relative bg-white rounded-lg shadow-lg w-full max-w-5xl max-h-[85vh] overflow-hidden z-10">
                <div class="flex items-center justify-between px-4 py-3 border-b">
                    <h3 class="text-lg font-semibold">{{ $clientesModalTitulo }}</h3>
                    <button type="button" wire:click="closeClientesModal" class="text-gray-500 hover:text-gray-700">Cerrar</button>
                </div>

                <div class="p-4 overflow-auto max-h-[70vh]">
                    @if(empty($clientesModalRows))
                        <p class="text-sm text-gray-600">No hay clientes para esta consulta.</p>
                    @else
                        <table class="min-w-full border border-gray-200 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="text-left px-3 py-2 border-b">ID Cliente</th>
                                    <th class="text-left px-3 py-2 border-b">Nombre</th>
                                    <th class="text-left px-3 py-2 border-b">CURP</th>
                                    <th class="text-left px-3 py-2 border-b">Préstamo</th>
                                    <th class="text-left px-3 py-2 border-b">Fecha entrega</th>
                                    <th class="text-left px-3 py-2 border-b">Asesor</th>
                                    <th class="text-left px-3 py-2 border-b">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clientesModalRows as $row)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-3 py-2">{{ $row['cliente_id'] }}</td>
                                        <td class="px-3 py-2">{{ $row['nombre'] }}</td>
                                        <td class="px-3 py-2">{{ $row['curp'] ?: 'N/A' }}</td>
                                        <td class="px-3 py-2">#{{ $row['prestamo_id'] }}</td>
                                        <td class="px-3 py-2">{{ $row['fecha_entrega'] ?: 'N/A' }}</td>
                                        <td class="px-3 py-2">{{ $row['asesor'] ?: 'N/A' }}</td>
                                        <td class="px-3 py-2">
                                            <a href="{{ route('prestamos.print', ['prestamo' => $row['prestamo_id'], 'type' => 'estado_cuenta']) }}" target="_blank" class="inline-flex items-center px-2 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700">
                                                Estado de cuenta
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($showClientesBucketModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" wire:click="closeClientesBucketModal"></div>
            <div class="relative bg-white rounded-lg shadow-lg w-full max-w-5xl max-h-[85vh] overflow-hidden z-10">
                <div class="flex items-center justify-between px-4 py-3 border-b">
                    <h3 class="text-lg font-semibold">{{ $clientesBucketTitulo }}</h3>
                    <button type="button" wire:click="closeClientesBucketModal" class="text-gray-500 hover:text-gray-700">Cerrar</button>
                </div>
                <div class="p-4 overflow-auto max-h-[70vh]">
                    @if(empty($clientesBucketRows))
                        <p class="text-sm text-gray-600">No hay clientes para este bucket.</p>
                    @else
                        <table class="min-w-full border border-gray-200 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="text-left px-3 py-2 border-b">ID Cliente</th>
                                    <th class="text-left px-3 py-2 border-b">Nombre</th>
                                    <th class="text-left px-3 py-2 border-b">CURP</th>
                                    <th class="text-left px-3 py-2 border-b">Préstamo</th>
                                    <th class="text-left px-3 py-2 border-b">Fecha entrega</th>
                                    <th class="text-left px-3 py-2 border-b">Asesor</th>
                                    <th class="text-left px-3 py-2 border-b">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clientesBucketRows as $row)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-3 py-2">{{ $row['cliente_id'] }}</td>
                                        <td class="px-3 py-2">{{ $row['nombre'] }}</td>
                                        <td class="px-3 py-2">{{ $row['curp'] ?: 'N/A' }}</td>
                                        <td class="px-3 py-2">#{{ $row['prestamo_id'] }}</td>
                                        <td class="px-3 py-2">{{ $row['fecha_entrega'] ?: 'N/A' }}</td>
                                        <td class="px-3 py-2">{{ $row['asesor'] ?: 'N/A' }}</td>
                                        <td class="px-3 py-2">
                                            <a href="{{ route('prestamos.print', ['prestamo' => $row['prestamo_id'], 'type' => 'estado_cuenta']) }}" target="_blank" class="inline-flex items-center px-2 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700">
                                                Estado de cuenta
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    @endif

</div>





