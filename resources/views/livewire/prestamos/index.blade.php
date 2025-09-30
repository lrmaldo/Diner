<div class="p-6 max-w-6xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Préstamos</h1>
        <a href="{{ route('prestamos.create') }}" class="btn-primary">Solicitar crédito</a>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <table class="w-full table-auto">
            <thead>
                <tr class="text-left">
                    <th class="px-2 py-3">Id</th>
                    <th class="px-2 py-3">Solicitante</th>
                    <th class="px-2 py-3">Monto</th>
                    <th class="px-2 py-3">Plazo</th>
                    <th class="px-2 py-3">Estado</th>
                    <th class="px-2 py-3">Fecha</th>
                    <th class="px-2 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prestamos as $p)
                    <tr class="border-t">
                        <td class="px-2 py-3">{{ $p->id }}</td>
                        <td class="px-2 py-3">
                            @php
                                $solicitante = $p->producto === 'individual' ? optional($p->cliente) : optional($p->representante);
                            @endphp
                            @if($solicitante)
                                {{ trim(($solicitante->nombres ?? '').' '.($solicitante->apellido_paterno ?? '')) }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-2 py-3">{{ number_format($p->monto ?? $p->monto_total ?? 0, 2) }}</td>
                        <td class="px-2 py-3">{{ $p->plazo }}</td>
                        <td class="px-2 py-3">
                            @php
                                $estado = $p->estado;
                                $map = [
                                    'en_curso' => 'bg-yellow-100 text-yellow-800',
                                    'en_revision' => 'bg-blue-100 text-blue-800',
                                    'autorizado' => 'bg-green-100 text-green-800',
                                    'rechazado' => 'bg-red-100 text-red-800',
                                ];
                                $cls = $map[$estado] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-block px-2 py-1 rounded text-sm {{ $cls }}">
                                {{ str_replace('_', ' ', $estado) }}
                            </span>
                        </td>
                        <td class="px-2 py-3">{{ $p->created_at->format('Y-m-d') }}</td>
                        <td class="px-2 py-3 text-right">
                            @if($p->estado === 'en_curso')
                                <a href="{{ route('prestamos.edit', $p->id) }}" class="btn-outline mr-2">Continuar</a>
                            @endif

                            @if($p->estado === 'en_curso' && auth()->user()->hasRole('Administrador'))
                                <button wire:click.prevent="autorizar({{ $p->id }})" class="btn-primary mr-2">Autorizar</button>
                                <button wire:click.prevent="rechazar({{ $p->id }})" class="btn-danger">Rechazar</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $prestamos->links() }}
        </div>
    </div>
</div>
