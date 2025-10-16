<div class="p-6 max-w-3xl mx-auto">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900">Detalle del préstamo</h2>

        @if($prestamo)
            <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">N.º Préstamo</dt>
                    <dd class="font-medium text-gray-900">{{ $prestamo->numero_prestamo ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Producto</dt>
                    <dd class="font-medium text-gray-900">{{ ucfirst($prestamo->producto ?? 'N/A') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Monto</dt>
                    <dd class="font-medium text-gray-900">${{ number_format($prestamo->monto ?? 0, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Estado</dt>
                    <dd class="font-medium text-gray-900">{{ ucfirst($prestamo->estado ?? 'N/A') }}</dd>
                </div>
            </dl>
        @else
            <p class="mt-2 text-sm text-gray-500">No se encontró el préstamo solicitado.</p>
        @endif
    </div>

    @if($prestamo && $prestamo->relationLoaded('clientes'))
        <div class="mt-6 bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Solicitantes</h3>
            <ul class="divide-y">
                @forelse($prestamo->clientes as $cliente)
                    <li class="py-3 text-sm flex justify-between">
                        <span>{{ $cliente->nombre ?? ($cliente->nombres ?? 'Cliente') }} {{ $cliente->apellido ?? ($cliente->apellido_paterno ?? '') }}</span>
                        <span>${{ number_format($cliente->pivot->monto_solicitado ?? 0, 2) }}</span>
                    </li>
                @empty
                    <li class="py-3 text-sm text-gray-500">Sin clientes asociados.</li>
                @endforelse
            </ul>
        </div>
    @endif

    @can('aprobar prestamos')
        @if($prestamo && ($prestamo->estado === 'pendiente'))
            <div class="mt-6 bg-white shadow rounded-lg p-6 flex gap-4">
                <button type="button" wire:click="autorizar" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-lg">Autorizar</button>
                <button type="button" wire:click="rechazar" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-6 rounded-lg">Rechazar</button>
            </div>
        @endif
    @endcan
</div>

                    </button>                                    @if($prestamo->cliente->capacidad_pago)

                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">

                    {{-- Botón Solicitar Más Información --}}                                            Sí

                    <button                                         </span>

                        class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center">                                    @else

                        <i class="fas fa-question-circle mr-2"></i>                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">

                        Solicitar Más Info                                            No

                    </button>                                        </span>

                </div>                                    @endif

                                </td>

                {{-- Notas del comité --}}                            </tr>

                <div class="mt-6">                        @endif

                    <label for="notas_comite" class="block text-sm font-medium text-gray-700 mb-2">                    @endif

                        Notas del Comité                </tbody>

                    </label>            </table>

                    <textarea         </div>

                        id="notas_comite"    </div>

                        rows="3"

                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"    {{-- Botones de Acción del Comité (solo para Admin) --}}

                        placeholder="Agregue comentarios o notas sobre la decisión del comité..."></textarea>    @can('aprobar prestamos')

                </div>        <div class="bg-white shadow rounded-lg p-6">

            @elseif($prestamo->estado === 'aprobado')            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">

                <p class="text-center text-sm text-gray-500 mt-4">                {{-- Botón Rechazar --}}

                    <i class="fas fa-check-circle mr-1 text-green-500"></i>                @if($prestamo->estado !== 'rechazado')

                    Este préstamo ha sido aprobado                    <button

                </p>                        wire:click="$dispatch('openModal', { component: 'prestamos.rechazar-modal', arguments: { prestamoId: {{ $prestamo->id }} }})"

            @elseif($prestamo->estado === 'rechazado')                        class="flex-1 sm:flex-none px-8 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors shadow-md hover:shadow-lg"

                <p class="text-center text-sm text-gray-500 mt-4">                    >

                    <i class="fas fa-info-circle mr-1"></i>                        <i class="fas fa-times-circle mr-2"></i>

                    Este préstamo ha sido rechazado                        Rechazar

                </p>                    </button>

            @endif                @endif

        </div>

    @endcan                {{-- Botón Editar --}}

</div>                <a
                    href="{{ route('prestamos.edit', $prestamo->id) }}"
                    class="flex-1 sm:flex-none px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-md hover:shadow-lg text-center"
                >
                    <i class="fas fa-edit mr-2"></i>
                    Editar
                </a>

                {{-- Botón Autorizar --}}
                @if($prestamo->estado !== 'autorizado' && $prestamo->estado !== 'rechazado')
                    <button
                        wire:click="autorizar"
                        wire:confirm="¿Está seguro de autorizar este préstamo?"
                        class="flex-1 sm:flex-none px-8 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors shadow-md hover:shadow-lg"
                    >
                        <i class="fas fa-check-circle mr-2"></i>
                        Autorizar
                    </button>
                @endif
            </div>

            @if($prestamo->estado === 'autorizado')
                <p class="text-center text-sm text-gray-500 mt-4">
                    <i class="fas fa-info-circle mr-1"></i>
                    Este préstamo ya ha sido autorizado
                </p>
            @elseif($prestamo->estado === 'rechazado')
                <p class="text-center text-sm text-gray-500 mt-4">
                    <i class="fas fa-info-circle mr-1"></i>
                    Este préstamo ha sido rechazado
                </p>
            @endif
        </div>
    @endcan
</div>
