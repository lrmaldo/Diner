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
                            @if($p->prestamoable)
                                @if($p->prestamoable_type === App\Models\Cliente::class)
                                    {{ $p->prestamoable->nombres ?? '' }} {{ $p->prestamoable->apellido_paterno ?? '' }}
                                @else
                                    {{ $p->prestamoable->nombre ?? '' }}
                                @endif
                            @endif
                        </td>
                        <td class="px-2 py-3">{{ number_format($p->monto, 2) }}</td>
                        <td class="px-2 py-3">{{ $p->plazo }}</td>
                        <td class="px-2 py-3">
                            <span class="inline-block px-2 py-1 rounded text-sm">
                                {{ $p->estado }}
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
