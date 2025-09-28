<div class="p-6 max-w-6xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Préstamos</h1>
        <a href="{{ route('prestamos.create') }}" class="btn-primary">Solicitar crédito</a>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <table class="w-full table-auto">
            <thead>
                <tr class="text-left">
                    <th>Id</th>
                    <th>Solicitante</th>
                    <th>Monto</th>
                    <th>Plazo</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prestamos as $p)
                    <tr class="border-t">
                        <td>{{ $p->id }}</td>
                        <td>
                            @if($p->prestamoable)
                                @if($p->prestamoable_type === App\Models\Cliente::class)
                                    {{ $p->prestamoable->nombres ?? '' }} {{ $p->prestamoable->apellido_paterno ?? '' }}
                                @else
                                    {{ $p->prestamoable->nombre ?? '' }}
                                @endif
                            @endif
                        </td>
                        <td>{{ number_format($p->monto, 2) }}</td>
                        <td>{{ $p->plazo }}</td>
                        <td>
                            <span class="inline-block px-2 py-1 rounded text-sm">
                                {{ $p->estado }}
                            </span>
                        </td>
                        <td>
                            @if($p->estado === 'en_curso')
                                <a href="{{ route('prestamos.edit', $p->id) }}" class="btn-outline">Continuar</a>
                            @endif
                        </td>
                        <td>{{ $p->created_at->format('Y-m-d') }}</td>
                        <td class="text-right">
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
