<div class="p-4 max-w-7xl mx-auto">
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna principal --}}
        <div class="lg:col-span-2 space-y-6">
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
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">CURP</th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Monto Solicitado</th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Rol</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($prestamo->clientes as $cliente)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <p class="font-medium text-gray-900">{{ trim($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno) }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $cliente->curp }}</td>
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
                    <h2 class="text-xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-user text-blue-600 mr-2"></i>
                        Información del Cliente
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Nombre Completo</label>
                            <p class="text-lg font-medium">{{ trim($prestamo->cliente->nombres . ' ' . $prestamo->cliente->apellido_paterno . ' ' . $prestamo->cliente->apellido_materno) }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">CURP</label>
                            <p class="text-lg font-medium">{{ $prestamo->cliente->curp ?? 'No disponible' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                            <p class="text-lg">{{ $prestamo->cliente->email ?? 'No disponible' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Estado Civil</label>
                            <p class="text-lg capitalize">{{ $prestamo->cliente->estado_civil ?? 'No especificado' }}</p>
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
        </div>

        {{-- Columna lateral - Historial y estadísticas --}}
        <div class="space-y-6">
            {{-- Porcentaje de cumplimiento --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4 flex items-center">
                    <i class="fas fa-chart-pie text-blue-600 mr-2"></i>
                    Cumplimiento de Pagos
                </h3>

                <div class="flex flex-col items-center">
                    <div class="relative w-32 h-32">
                        <svg class="w-full h-full transform -rotate-90">
                            <circle cx="64" cy="64" r="56" stroke="#e5e7eb" stroke-width="8" fill="none" />
                            <circle
                                cx="64"
                                cy="64"
                                r="56"
                                stroke="{{ $porcentajeCumplimiento >= 80 ? '#10b981' : ($porcentajeCumplimiento >= 50 ? '#f59e0b' : '#ef4444') }}"
                                stroke-width="8"
                                fill="none"
                                stroke-dasharray="{{ 2 * 3.14159 * 56 }}"
                                stroke-dashoffset="{{ 2 * 3.14159 * 56 * (1 - $porcentajeCumplimiento / 100) }}"
                                stroke-linecap="round"
                            />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-3xl font-bold {{ $porcentajeCumplimiento >= 80 ? 'text-green-600' : ($porcentajeCumplimiento >= 50 ? 'text-orange-600' : 'text-red-600') }}">
                                {{ $porcentajeCumplimiento }}%
                            </span>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-gray-600 text-center">
                        Porcentaje basado en préstamos anteriores
                    </p>
                </div>
            </div>

            {{-- Historial Crediticio --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4 flex items-center">
                    <i class="fas fa-history text-blue-600 mr-2"></i>
                    Historial Crediticio
                </h3>

                @if($historialPrestamos->isEmpty())
                    <div class="text-center py-8">
                        <svg class="h-16 w-16 mx-auto text-gray-400 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-gray-500 text-sm">Sin historial crediticio disponible</p>
                        <p class="text-gray-400 text-xs mt-1">Este es el primer préstamo registrado</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($historialPrestamos as $hist)
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
                            @endphp
                            <div class="border rounded-lg p-3 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-grow">
                                        <p class="font-medium text-gray-900">Préstamo #{{ $hist->id }}</p>
                                        <p class="text-xs text-gray-500">{{ $hist->created_at->format('d/m/Y') }}</p>
                                    </div>
                                    <span class="text-xs capitalize {{ $colorBg }} text-white px-2 py-1 rounded">
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
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="{{ $colorBg }} h-2 rounded-full" style="width: {{ $hist->estado === 'autorizado' ? '100' : ($hist->estado === 'en_revision' ? '60' : '30') }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($prestamo->producto === 'individual' && $prestamo->cliente_id)
                        <a href="{{ route('clientes.historial', $prestamo->cliente_id) }}" class="block mt-4 text-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Ver historial completo <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    @elseif($prestamo->representante_id)
                        <a href="{{ route('clientes.historial', $prestamo->representante_id) }}" class="block mt-4 text-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Ver historial completo <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
