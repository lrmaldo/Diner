<div class="p-4 max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold dark:text-white">Aclaración de pagos</h1>
    </div>

    <!-- Search Box -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
        <form wire:submit.prevent="search" class="flex flex-col sm:flex-row items-end gap-4">
            <div class="w-full sm:w-auto flex-1 max-w-md">
                <label for="grupoSearch" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Grupo</label>
                <div class="relative">
                    <input type="text" 
                        wire:model="grupoSearch" 
                        id="grupoSearch" 
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Ingrese el grupo">
                </div>
            </div>
            
            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-transparent tex-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Buscar
            </button>
        </form>

        @if($errorMessage)
            <div class="mt-4 p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 font-semibold" role="alert">
                <span class="font-medium">Error:</span> {{ $errorMessage }}
            </div>
        @endif
        
        @if(session()->has('success'))
            <div class="mt-4 text-green-600 font-semibold">
                {{ session('success') }}
            </div>
        @endif
    </div>

    @if($prestamo && count($clientData) > 0)
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <!-- Table Header Custom -->
        <div class="bg-red-600 text-white px-4 py-2 flex flex-col sm:flex-row justify-between items-center font-bold">
            <div class="text-lg">{{ $groupName }}</div>
            <div class="flex items-center space-x-2 mt-2 sm:mt-0">
                <span>Pago completo</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.live="fullPayment" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-red-600 text-white">
                    <tr>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider border-r border-red-500">Nombre</th>
                        <th scope="col" class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider border-r border-red-500">N. Pago</th>
                        <th scope="col" class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wider border-r border-red-500">Importe</th>
                        <th scope="col" class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wider border-r border-red-500">Pendiente</th>
                        <th scope="col" class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wider border-r border-red-500">Saldo</th>
                        <th scope="col" class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wider border-r border-red-500">Moratorio</th> <!-- Accrued fine? -->
                        <th scope="col" class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wider border-r border-red-500">Efectivo</th>
                        <th scope="col" class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wider">Moratorio</th> <!-- Pay fine -->
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($clientData as $clientId => $data)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 text-sm">
                        <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-white font-medium">
                            {{ $data['nombre'] }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-center text-gray-700 dark:text-gray-300">
                            {{ $data['numero_pago'] }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700 dark:text-gray-300">
                            {{ number_format($data['importe'], 0) }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700 dark:text-gray-300">
                            {{ number_format($data['pendiente'], 0) }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700 dark:text-gray-300">
                            {{ number_format($data['saldo'], 0) }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700 dark:text-gray-300">
                            {{ number_format(0, 0) }} <!-- Placeholder for accrued fines -->
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-right font-bold text-blue-600">
                            <input type="number" 
                                wire:model="inputs.{{ $clientId }}.efectivo" 
                                class="w-24 text-right border-0 border-b border-blue-300 focus:ring-0 focus:border-blue-600 bg-transparent py-1 px-0 font-bold text-blue-600"
                                placeholder="0">
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-right font-bold text-blue-600">
                            <input type="number" 
                                wire:model="inputs.{{ $clientId }}.moratorio" 
                                class="w-24 text-right border-0 border-b border-blue-300 focus:ring-0 focus:border-blue-600 bg-transparent py-1 px-0 font-bold text-blue-600"
                                placeholder="0">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 flex justify-center space-x-4">
            <button wire:click="cancel" class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Cancelar
            </button>
            
            <button wire:click="aclarar" class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Aclarar
            </button>
        </div>
    </div>
    @endif
</div>

