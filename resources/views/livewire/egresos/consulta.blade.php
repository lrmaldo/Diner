<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Egresos</h1>
            <p class="mt-1 text-sm text-gray-500">Consulta de egresos registrados por periodo.</p>
        </div>

        <div class="rounded-xl shadow-sm overflow-hidden mb-8">
            <div class="bg-blue-600 px-6 py-3">
                <h2 class="text-white text-center font-bold uppercase tracking-wide">Periodo de la Consulta</h2>
            </div>
            <div class="bg-white p-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                    <div>
                        <label for="desde" class="block text-sm font-medium text-gray-700">Desde el:</label>
                        <input type="date" id="desde" wire:model="desde"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('desde') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="hasta" class="block text-sm font-medium text-gray-700">Hasta el:</label>
                        <input type="date" id="hasta" wire:model="hasta"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('hasta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <button wire:click="generar"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-wide hover:bg-blue-700">
                            Generar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if($generado)
            @if(count($egresosPorMes) > 0)
                @foreach($egresosPorMes as $mes => $datosMes)
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-2 capitalize">{{ $datosMes['etiqueta'] }}</h3>
                        <div class="overflow-x-auto bg-white rounded-lg shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($datosMes['egresos'] as $egreso)
                                        <tr>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $egreso->created_at->format('d-m-y') }}</td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900">{{ $egreso->descripcion }}</td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $egreso->user->name }}</td>
                                            <td class="px-6 py-3 whitespace-nowrap text-right text-sm text-gray-900">${{ number_format($egreso->monto, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-blue-50">
                                    <tr>
                                        <td colspan="3" class="px-6 py-3 text-right text-sm font-bold text-blue-800 uppercase">Total</td>
                                        <td class="px-6 py-3 text-right text-sm font-bold text-blue-900">${{ number_format($datosMes['total'], 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endforeach

                @if(count($egresosPorMes) > 1)
                    <div class="mt-2 bg-blue-700 rounded-lg shadow-sm">
                        <div class="px-6 py-3 flex justify-end items-center gap-4">
                            <span class="text-white font-bold uppercase">Total</span>
                            <span class="text-white text-xl font-bold">${{ number_format($totalGeneral, 2) }}</span>
                        </div>
                    </div>
                @endif
            @else
                <div class="bg-white rounded-lg shadow-sm p-8 text-center text-gray-500">
                    No hay egresos para el período seleccionado.
                </div>
            @endif
        @endif
    </div>
</div>
