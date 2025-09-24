<div class="p-6 max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-project-700">Detalle del cliente</h1>
        <a href="{{ route('clients.index') }}" class="btn-outline">Volver</a>
    </div>

    <div class="bg-white shadow rounded-lg p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Nombre</h3>
                <div class="text-lg text-gray-900">{{ $cliente->nombre }} {{ $cliente->apellido }}</div>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500">Email</h3>
                <div class="text-lg text-gray-900">{{ $cliente->email ?? '-' }}</div>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500">CURP</h3>
                <div class="text-lg text-gray-900">{{ $cliente->curp ?? '-' }}</div>
            </div>

            <div class="sm:col-span-2">
                <h3 class="text-sm font-medium text-gray-500">Teléfonos</h3>
                <div class="space-y-2">
                    @forelse($cliente->telefonos as $tel)
                        <div class="text-lg text-gray-900">{{ ucfirst($tel->tipo) }}: {{ $tel->numero }}</div>
                    @empty
                        <div class="text-lg text-gray-900">-</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Dirección</h3>
            <div class="text-gray-900">{{ $cliente->direccion ?? '-' }}</div>
        </div>
    </div>
</div>
