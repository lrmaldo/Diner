<div class="p-4 max-w-7xl mx-auto" x-data="{
    clienteSeleccionado: null,
    nombreCliente: 'Todos los clientes',
    seleccionarCliente(id, nombre) {
        this.clienteSeleccionado = id;
        this.nombreCliente = nombre;
        // Hacer scroll al historial
        document.getElementById('historial-crediticio').scrollIntoView({ behavior: 'smooth', block: 'start' });
    },
    limpiarSeleccion() {
        this.clienteSeleccionado = null;
        this.nombreCliente = 'Todos los clientes';
    }
}">
    {{-- Encabezado --}}
    <div class="mb-6 bg-white shadow rounded-lg p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex-grow">
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-3xl font-bold text-gray-900">
                        Préstamo #{{ $prestamo->id }}
                    </h1>
                    @php
                        $estado = $prestamo->estado;
                        $map = [
                            'en_curso' => 'bg-yellow-100 text-yellow-800',
                            'en_revision' => 'bg-blue-100 text-blue-800',
                            'autorizado' => 'bg-green-100 text-green-800',
                            'rechazado' => 'bg-red-100 text-red-800',
                        ];
                        $cls = $map[$estado] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $cls }}">
                        {{ ucfirst(str_replace('_', ' ', $estado)) }}
                    </span>
                </div>

                @if($prestamo->producto === 'individual')
                    <p class="text-xl text-gray-700">
                        Cliente: <span class="font-semibold">{{ trim(($prestamo->cliente->nombres ?? '') . ' ' . ($prestamo->cliente->apellido_paterno ?? '') . ' ' . ($prestamo->cliente->apellido_materno ?? '')) }}</span>
                    </p>
                @else
                    <p class="text-xl text-gray-700">
                        Préstamo Grupal - Representante: <span class="font-semibold">{{ trim(($prestamo->representante->nombres ?? '') . ' ' . ($prestamo->representante->apellido_paterno ?? '')) }}</span>
                    </p>
                @endif

                <p class="text-sm text-gray-500 mt-1">
                    Fecha de solicitud: {{ $prestamo->created_at->format('d/m/Y H:i') }}
                </p>
            </div>

            {{-- Botones de acción --}}
            <div class="flex flex-col gap-2">
                <a href="{{ route('prestamos.edit', $prestamo->id) }}" class="btn-primary text-center">
                    <i class="fas fa-edit mr-1"></i> Editar préstamo
                </a>
                <a href="{{ route('prestamos.index') }}" class="btn-outline text-center">
                    <i class="fas fa-arrow-left mr-1"></i> Regresar
                </a>
                @if($prestamo->producto === 'individual' && $prestamo->cliente_id)
                    <a href="{{ route('clientes.historial', $prestamo->cliente_id) }}" class="btn-outline text-center">
                        <i class="fas fa-history mr-1"></i> Ver historial completo
                    </a>
                @elseif($prestamo->representante_id)
                    <a href="{{ route('clientes.historial', $prestamo->representante_id) }}" class="btn-outline text-center">
                        <i class="fas fa-history mr-1"></i> Ver historial completo
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Columna principal - Ahora ocupa todo el ancho --}}
    <div class="space-y-6">
            {{-- Datos generales del préstamo --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4 flex items-center">
                    <i class="fas fa-file-contract text-blue-600 mr-2"></i>
                    Datos Generales del Préstamo
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Tipo de Producto</label>
                        <p class="text-lg font-medium capitalize">{{ $prestamo->producto }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Plazo</label>
                        <p class="text-lg font-medium">{{ $prestamo->plazo }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Periodicidad</label>
                        <p class="text-lg font-medium capitalize">{{ $prestamo->periodicidad }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Día de Pago</label>
                        <p class="text-lg font-medium capitalize">{{ $prestamo->dia_pago }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Fecha de Entrega</label>
                        <p class="text-lg font-medium">{{ optional($prestamo->fecha_entrega)->format('d/m/Y') ?? 'No especificada' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Fecha Primer Pago</label>
                        <p class="text-lg font-medium">{{ optional($prestamo->fecha_primer_pago)->format('d/m/Y') ?? 'No especificada' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Tasa de Interés</label>
                        <p class="text-lg font-medium">{{ number_format($prestamo->tasa_interes, 2) }}% <span class="text-sm text-gray-500">+ IVA</span></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Monto Total</label>
                        <p class="text-2xl font-bold text-green-600">${{ number_format($prestamo->monto_total ?? 0, 2) }}</p>
                    </div>
                </div>

                @if($prestamo->estado === 'rechazado' && $prestamo->motivo_rechazo)
                    <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <h3 class="text-sm font-medium text-red-800 mb-2">Motivo de Rechazo:</h3>
                        <p class="text-sm text-red-700">{{ $prestamo->motivo_rechazo }}</p>
                        @if($prestamo->autorizador)
                            <p class="text-xs text-red-600 mt-2">Rechazado por: {{ $prestamo->autorizador->name }}</p>
                        @endif
                    </div>
                @endif

                @if($prestamo->estado === 'autorizado' && $prestamo->autorizador)
                    <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-green-700">
                            <i class="fas fa-check-circle mr-1"></i>
                            Autorizado por: <span class="font-medium">{{ $prestamo->autorizador->name }}</span>
                        </p>
                    </div>
                @endif
            </div>

            {{-- Lista de clientes (si es grupal) --}}
            @if($prestamo->producto === 'grupal')
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-users text-blue-600 mr-2"></i>
                        Integrantes del Grupo ({{ $prestamo->clientes->count() }})
                    </h2>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nombre</th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Historial</th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Monto Solicitado</th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Rol</th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($prestamo->clientes as $cliente)
                                    @php
                                        // Obtener historial de préstamos del cliente
                                        $historialCliente = \App\Models\Prestamo::where(function($query) use ($cliente) {
                                            $query->where('cliente_id', $cliente->id)
                                                  ->orWhereHas('clientes', function($q) use ($cliente) {
                                                      $q->where('clientes.id', $cliente->id);
                                                  });
                                        })
                                        ->where('id', '!=', $prestamo->id)
                                        ->orderBy('created_at', 'desc')
                                        ->limit(10)
                                        ->get();
                                    @endphp
                                    <tr class="hover:bg-gray-50" :class="{ 'bg-blue-50': clienteSeleccionado === {{ $cliente->id }} }">
                                        <td class="px-4 py-3">
                                            <p
                                                class="font-medium text-gray-900 cursor-pointer hover:text-blue-600 transition-colors"
                                                @click="seleccionarCliente({{ $cliente->id }}, '{{ trim($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno) }}')"
                                                title="Click para ver el historial de este cliente"
                                            >
                                                {{ trim($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno) }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($historialCliente->isEmpty())
                                                <span class="text-xs text-gray-400 italic">Sin historial</span>
                                            @else
                                                <div class="flex items-center justify-center gap-3">
                                                    <div class="flex gap-1 items-end h-16" title="{{ $historialCliente->count() }} préstamos anteriores">
                                                        @foreach($historialCliente as $hist)
                                                            @php
                                                                $colorBarra = match($hist->estado) {
                                                                    'autorizado' => 'bg-green-500',
                                                                    'rechazado' => 'bg-red-500',
                                                                    'en_revision' => 'bg-yellow-500',
                                                                    'en_curso' => 'bg-blue-500',
                                                                    default => 'bg-gray-400'
                                                                };
                                                                // Altura proporcional al monto (normalizado entre 30% y 100%)
                                                                $maxMonto = $historialCliente->max('monto_total') ?: 1;
                                                                $altura = max(30, ($hist->monto_total / $maxMonto) * 100);
                                                            @endphp
                                                            <div
                                                                class="w-3.5 {{ $colorBarra }} rounded-t transition-all hover:opacity-75 hover:scale-110 cursor-pointer shadow-sm"
                                                                style="height: {{ $altura }}%"
                                                                title="Préstamo #{{ $hist->id }} - ${{ number_format($hist->monto_total, 2) }} - {{ ucfirst(str_replace('_', ' ', $hist->estado)) }}"
                                                        ></div>
                                                    @endforeach
                                                </div>
                                                @php
                                                    $totalPrestamosCliente = $historialCliente->count();
                                                @endphp
                                                <div class="flex flex-col justify-center">
                                                    <div class="text-center">
                                                        <p class="text-3xl font-bold text-blue-600">{{ $totalPrestamosCliente }}</p>
                                                        <p class="text-xs text-gray-500 mt-1">préstamo{{ $totalPrestamosCliente !== 1 ? 's' : '' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-green-600">
                                            ${{ number_format($cliente->pivot->monto_solicitado ?? 0, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($prestamo->representante_id == $cliente->id)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-star mr-1"></i> Representante
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-500">Integrante</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <a href="{{ route('clients.show', $cliente->id) }}"
                                               class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors"
                                               title="Ver datos completos del cliente">
                                                <i class="fas fa-eye mr-1"></i> Ver datos
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Datos del cliente (si es individual) --}}
            @if($prestamo->producto === 'individual' && $prestamo->cliente)
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold flex items-center">
                            <i class="fas fa-user text-blue-600 mr-2"></i>
                            Información del Cliente
                        </h2>
                        <a href="{{ route('clients.show', $prestamo->cliente->id) }}"
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <i class="fas fa-eye mr-2"></i> Ver datos completos
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Nombre Completo</label>
                            <p
                                class="text-lg font-medium cursor-pointer hover:text-blue-600 transition-colors"
                                @click="seleccionarCliente({{ $prestamo->cliente->id }}, '{{ trim($prestamo->cliente->nombres . ' ' . $prestamo->cliente->apellido_paterno . ' ' . $prestamo->cliente->apellido_materno) }}')"
                                title="Click para ver el historial"
                            >
                                {{ trim($prestamo->cliente->nombres . ' ' . $prestamo->cliente->apellido_paterno . ' ' . $prestamo->cliente->apellido_materno) }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                            <p class="text-lg">{{ $prestamo->cliente->email ?? 'No disponible' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Estado Civil</label>
                            <p class="text-lg capitalize">{{ $prestamo->cliente->estado_civil ?? 'No especificado' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Historial Crediticio y Monto</label>
                            @php
                                // Obtener historial de préstamos del cliente individual
                                $historialClienteIndividual = \App\Models\Prestamo::where(function($query) use ($prestamo) {
                                    $query->where('cliente_id', $prestamo->cliente->id)
                                          ->orWhereHas('clientes', function($q) use ($prestamo) {
                                              $q->where('clientes.id', $prestamo->cliente->id);
                                          });
                                })
                                ->where('id', '!=', $prestamo->id)
                                ->orderBy('created_at', 'desc')
                                ->limit(10)
                                ->get();

                                $totalPrestamosCliente = $historialClienteIndividual->count();
                            @endphp
                            <div class="flex items-center gap-4">
                                @if($historialClienteIndividual->isEmpty())
                                    <span class="text-sm text-gray-400 italic">Sin historial previo</span>
                                @else
                                    <div class="flex items-end gap-3">
                                        <div class="flex gap-1 items-end h-16" title="{{ $totalPrestamosCliente }} préstamos anteriores">
                                            @foreach($historialClienteIndividual as $hist)
                                                @php
                                                    $colorBarra = match($hist->estado) {
                                                    'autorizado' => 'bg-green-500',
                                                    'rechazado' => 'bg-red-500',
                                                    'en_revision' => 'bg-yellow-500',
                                                    'en_curso' => 'bg-blue-500',
                                                    default => 'bg-gray-400'
                                                };
                                                // Altura proporcional al monto (normalizado entre 30% y 100%)
                                                $maxMonto = $historialClienteIndividual->max('monto_total') ?: 1;
                                                $altura = max(30, ($hist->monto_total / $maxMonto) * 100);
                                            @endphp
                                            <div
                                                class="w-3.5 {{ $colorBarra }} rounded-t transition-all hover:opacity-75 hover:scale-110 cursor-pointer shadow-sm"
                                                style="height: {{ $altura }}%"
                                                title="Préstamo #{{ $hist->id }} - ${{ number_format($hist->monto_total, 2) }} - {{ ucfirst(str_replace('_', ' ', $hist->estado)) }}"
                                            ></div>
                                        @endforeach
                                        </div>
                                        <div class="flex flex-col justify-center">
                                            <div class="text-center">
                                                <p class="text-3xl font-bold text-blue-600">{{ $totalPrestamosCliente }}</p>
                                                <p class="text-xs text-gray-500 mt-1">préstamo{{ $totalPrestamosCliente != 1 ? 's' : '' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="border-l-2 border-gray-300 pl-4 ml-2">
                                    <div class="text-center">
                                        <p class="text-sm text-gray-500 mb-1">Monto</p>
                                        <p class="text-2xl font-bold text-green-600">${{ number_format($prestamo->monto_total, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Domicilio</label>
                            <p class="text-lg">
                                {{ $prestamo->cliente->calle_numero ?? 'No disponible' }}
                                @if($prestamo->cliente->colonia || $prestamo->cliente->municipio || $prestamo->cliente->estado)
                                    <br>
                                    <span class="text-sm text-gray-600">
                                        {{ $prestamo->cliente->colonia ? $prestamo->cliente->colonia . ', ' : '' }}
                                        {{ $prestamo->cliente->municipio ? $prestamo->cliente->municipio . ', ' : '' }}
                                        {{ $prestamo->cliente->estado ?? '' }}
                                        {{ $prestamo->cliente->codigo_postal ? ' - CP ' . $prestamo->cliente->codigo_postal : '' }}
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Historial Crediticio Mejorado con Alpine.js --}}
            <div id="historial-crediticio" class="bg-white shadow rounded-lg p-6" x-data="{ expanded: false }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-history text-blue-600 mr-2"></i>
                        Historial Crediticio
                    </h3>

                    {{-- Indicador de filtro activo --}}
                    <div x-show="clienteSeleccionado !== null" class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">Mostrando historial de:</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800" x-text="nombreCliente"></span>
                        <button
                            @click="limpiarSeleccion()"
                            class="text-xs text-red-600 hover:text-red-800 font-medium"
                            title="Ver todos los préstamos"
                        >
                            <i class="fas fa-times-circle mr-1"></i> Limpiar filtro
                        </button>
                    </div>
                </div>

                @if($historialPrestamos->isEmpty())
                    <div class="text-center py-8">
                        <svg class="h-16 w-16 mx-auto text-gray-400 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-gray-500 text-sm">Sin historial crediticio disponible</p>
                        <p class="text-gray-400 text-xs mt-1">Este es el primer préstamo registrado</p>
                    </div>
                @else
                    <div class="space-y-3" x-data="{
                        countVisible() {
                            if (this.clienteSeleccionado === null) return {{ $historialPrestamos->count() }};
                            return [...document.querySelectorAll('[data-clientes]')]
                                .filter(el => el.getAttribute('data-clientes').split(',').includes(this.clienteSeleccionado.toString()))
                                .length;
                        }
                    }">
                        {{-- Mensaje cuando no hay resultados filtrados --}}
                        <div
                            x-show="clienteSeleccionado !== null && countVisible() === 0"
                            class="text-center py-8"
                        >
                            <svg class="h-16 w-16 mx-auto text-gray-400 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <p class="text-gray-500 text-sm">No se encontraron préstamos anteriores</p>
                            <p class="text-gray-400 text-xs mt-1" x-text="'para ' + nombreCliente"></p>
                            <button
                                @click="limpiarSeleccion()"
                                class="mt-3 text-sm text-blue-600 hover:text-blue-800 font-medium"
                            >
                                <i class="fas fa-arrow-left mr-1"></i> Ver todos los préstamos
                            </button>
                        </div>

                        {{-- Mostrar primeros 3 préstamos --}}
                        @foreach($historialPrestamos->take(3) as $index => $hist)
                            @php
                                $colorMap = [
                                    'autorizado' => 'green',
                                    'en_revision' => 'orange',
                                    'rechazado' => 'red',
                                    'en_curso' => 'blue',
                                ];
                                $color = $colorMap[$hist->estado] ?? 'gray';
                                $colorBg = [
                                    'green' => 'bg-green-500',
                                    'orange' => 'bg-orange-500',
                                    'red' => 'bg-red-500',
                                    'blue' => 'bg-blue-500',
                                    'gray' => 'bg-gray-500',
                                ][$color];
                                $colorText = [
                                    'green' => 'text-green-700',
                                    'orange' => 'text-orange-700',
                                    'red' => 'text-red-700',
                                    'blue' => 'text-blue-700',
                                    'gray' => 'text-gray-700',
                                ][$color];

                                // Determinar IDs de clientes relacionados
                                $clientesIds = [];
                                if ($hist->cliente_id) {
                                    $clientesIds[] = $hist->cliente_id;
                                }
                                if ($hist->producto === 'grupal' && $hist->clientes) {
                                    $clientesIds = array_merge($clientesIds, $hist->clientes->pluck('id')->toArray());
                                }
                                if ($hist->representante_id) {
                                    $clientesIds[] = $hist->representante_id;
                                }
                                $clientesIdsStr = implode(',', array_unique($clientesIds));
                            @endphp
                            <div
                                class="border rounded-lg p-3 hover:bg-gray-50 transition-colors cursor-pointer"
                                onclick="window.location.href='{{ route('prestamos.show', $hist->id) }}'"
                                data-clientes="{{ $clientesIdsStr }}"
                                x-show="clienteSeleccionado === null || ({{ !empty($clientesIdsStr) ? 1 : 0 }} && '{{ $clientesIdsStr }}'.split(',').includes(clienteSeleccionado.toString()))"
                                x-transition
                            >
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-grow">
                                        <p class="font-medium text-gray-900">Préstamo #{{ $hist->id }}</p>
                                        <p class="text-xs text-gray-500">
                                            <i class="far fa-calendar mr-1"></i>{{ $hist->created_at->format('d/m/Y') }}
                                            <span class="mx-2">•</span>
                                            <i class="far fa-clock mr-1"></i>{{ $hist->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <span class="text-xs capitalize {{ $colorBg }} text-white px-2 py-1 rounded whitespace-nowrap">
                                        {{ str_replace('_', ' ', $hist->estado) }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-xs mb-2">
                                    <div>
                                        <span class="text-gray-500">Tipo:</span>
                                        <span class="font-medium capitalize">{{ $hist->producto }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Plazo:</span>
                                        <span class="font-medium">{{ $hist->plazo }}</span>
                                    </div>
                                </div>

                                <div class="text-sm font-semibold text-green-600 mb-2">
                                    ${{ number_format($hist->monto_total ?? 0, 2) }}
                                </div>

                                {{-- Barra de comportamiento --}}
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="{{ $colorBg }} h-2 rounded-full transition-all duration-500" style="width: {{ $hist->estado === 'autorizado' ? '100' : ($hist->estado === 'en_revision' ? '60' : '30') }}%"></div>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1 {{ $colorText }}">
                                    @if($hist->estado === 'autorizado')
                                        ✓ Puntualidad: Excelente
                                    @elseif($hist->estado === 'en_revision')
                                        ⏳ En revisión por el comité
                                    @else
                                        ✗ No autorizado
                                    @endif
                                </p>
                            </div>
                        @endforeach

                        {{-- Préstamos adicionales (ocultos por defecto) --}}
                        @if($historialPrestamos->count() > 3)
                            <div x-show="expanded" x-collapse>
                                @foreach($historialPrestamos->skip(3) as $index => $hist)
                                    @php
                                        $colorMap = [
                                            'autorizado' => 'green',
                                            'en_revision' => 'orange',
                                            'rechazado' => 'red',
                                            'en_curso' => 'blue',
                                        ];
                                        $color = $colorMap[$hist->estado] ?? 'gray';
                                        $colorBg = [
                                            'green' => 'bg-green-500',
                                            'orange' => 'bg-orange-500',
                                            'red' => 'bg-red-500',
                                            'blue' => 'bg-blue-500',
                                            'gray' => 'bg-gray-500',
                                        ][$color];
                                        $colorText = [
                                            'green' => 'text-green-700',
                                            'orange' => 'text-orange-700',
                                            'red' => 'text-red-700',
                                            'blue' => 'text-blue-700',
                                            'gray' => 'text-gray-700',
                                        ][$color];

                                        // Determinar IDs de clientes relacionados
                                        $clientesIds = [];
                                        if ($hist->cliente_id) {
                                            $clientesIds[] = $hist->cliente_id;
                                        }
                                        if ($hist->producto === 'grupal' && $hist->clientes) {
                                            $clientesIds = array_merge($clientesIds, $hist->clientes->pluck('id')->toArray());
                                        }
                                        if ($hist->representante_id) {
                                            $clientesIds[] = $hist->representante_id;
                                        }
                                        $clientesIdsStr = implode(',', array_unique($clientesIds));
                                    @endphp
                                    <div
                                        class="border rounded-lg p-3 hover:bg-gray-50 transition-colors cursor-pointer"
                                        onclick="window.location.href='{{ route('prestamos.show', $hist->id) }}'"
                                        data-clientes="{{ $clientesIdsStr }}"
                                        x-show="clienteSeleccionado === null || ({{ !empty($clientesIdsStr) ? 1 : 0 }} && '{{ $clientesIdsStr }}'.split(',').includes(clienteSeleccionado.toString()))"
                                        x-transition
                                    >
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-grow">
                                                <p class="font-medium text-gray-900">Préstamo #{{ $hist->id }}</p>
                                                <p class="text-xs text-gray-500">
                                                    <i class="far fa-calendar mr-1"></i>{{ $hist->created_at->format('d/m/Y') }}
                                                    <span class="mx-2">•</span>
                                                    <i class="far fa-clock mr-1"></i>{{ $hist->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                            <span class="text-xs capitalize {{ $colorBg }} text-white px-2 py-1 rounded whitespace-nowrap">
                                                {{ str_replace('_', ' ', $hist->estado) }}
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 text-xs mb-2">
                                            <div>
                                                <span class="text-gray-500">Tipo:</span>
                                                <span class="font-medium capitalize">{{ $hist->producto }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Plazo:</span>
                                                <span class="font-medium">{{ $hist->plazo }}</span>
                                            </div>
                                        </div>

                                        <div class="text-sm font-semibold text-green-600 mb-2">
                                            ${{ number_format($hist->monto_total ?? 0, 2) }}
                                        </div>

                                        {{-- Barra de comportamiento --}}
                                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                            <div class="{{ $colorBg }} h-2 rounded-full transition-all duration-500" style="width: {{ $hist->estado === 'autorizado' ? '100' : ($hist->estado === 'en_revision' ? '60' : '30') }}%"></div>
                                        </div>
                                        <p class="text-[10px] text-gray-400 mt-1 {{ $colorText }}">
                                            @if($hist->estado === 'autorizado')
                                                ✓ Puntualidad: Excelente
                                            @elseif($hist->estado === 'en_revision')
                                                ⏳ En revisión por el comité
                                            @else
                                                ✗ No autorizado
                                            @endif
                                        </p>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Botón para expandir/colapsar --}}
                            <button
                                @click="expanded = !expanded"
                                class="w-full mt-3 py-2 px-4 border border-blue-300 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors flex items-center justify-center gap-2 font-medium"
                            >
                                <span x-show="!expanded">
                                    <i class="fas fa-chevron-down"></i>
                                    Ver historial completo ({{ $historialPrestamos->count() }} créditos)
                                </span>
                                <span x-show="expanded" x-cloak>
                                    <i class="fas fa-chevron-up"></i>
                                    Ver menos
                                </span>
                            </button>
                        @endif
                    </div>

                    @if($prestamo->producto === 'individual' && $prestamo->cliente_id)
                        <a href="{{ route('clientes.historial', $prestamo->cliente_id) }}" class="block mt-4 text-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                            <i class="fas fa-external-link-alt mr-1"></i> Abrir historial completo en nueva página
                        </a>
                    @elseif($prestamo->representante_id)
                        <a href="{{ route('clientes.historial', $prestamo->representante_id) }}" class="block mt-4 text-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                            <i class="fas fa-external-link-alt mr-1"></i> Abrir historial completo en nueva página
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
