<div class="p-6 max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Sumar Capital a Caja</h1>
        <p class="text-sm text-gray-600">Registre las denominaciones de dinero físico que se ingresan como nuevo capital.</p>
    </div>

    <!-- Panel Total -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 sticky top-0 z-10 shadow-md">
        <div class="flex justify-between items-center">
            <div>
                <span class="text-gray-700 font-medium">Monto Total a Ingresar:</span>
            </div>
            <div class="text-3xl font-bold text-blue-700">
                ${{ number_format($this->totalGeneral, 2) }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Billetes -->
        <div class="bg-white shadow rounded-lg p-6 border-t-4 border-green-400">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h2 class="text-lg font-bold text-green-800">Billetes</h2>
                </div>
                <span class="font-bold text-green-600">${{ number_format($this->totalBilletes, 2) }}</span>
            </div>

            <div class="space-y-4">
                @foreach([
                    ['denom' => '1000', 'img' => null, 'label' => '$1,000'],
                    ['denom' => '500', 'img' => null, 'label' => '$500'],
                    ['denom' => '200', 'img' => null, 'label' => '$200'],
                    ['denom' => '100', 'img' => null, 'label' => '$100'],
                    ['denom' => '50', 'img' => null, 'label' => '$50'],
                    ['denom' => '20', 'img' => null, 'label' => '$20'],
                ] as $billete)
                <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition-colors">
                    <div class="flex items-center gap-4 w-1/3">
                        {{-- Placeholder para imagen de billete --}}
                        <div class="w-16 h-8 bg-green-100 border border-green-200 rounded flex items-center justify-center text-xs text-green-800 font-bold shadow-sm">
                            {{ $billete['label'] }}
                        </div>
                    </div>
                    
                    <div class="w-1/3 flex justify-center">
                        <input type="number" 
                               min="0" 
                               wire:model.live="billetes.{{ $billete['denom'] }}" 
                               class="w-20 text-center border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 shadow-sm"
                               placeholder="0">
                    </div>

                    <div class="w-1/3 text-right font-medium text-gray-700">
                        ${{ number_format((float)$billetes[$billete['denom']] * (float)$billete['denom'], 0) }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Monedas -->
        <div class="bg-white shadow rounded-lg p-6 border-t-4 border-yellow-400">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="text-lg font-bold text-yellow-800">Monedas</h2>
                </div>
                <span class="font-bold text-yellow-600">${{ number_format($this->totalMonedas, 2) }}</span>
            </div>

            <div class="space-y-4">
                @foreach([
                    ['denom' => '20', 'img' => null, 'label' => '$20'],
                    ['denom' => '10', 'img' => null, 'label' => '$10'],
                    ['denom' => '5', 'img' => null, 'label' => '$5'],
                    ['denom' => '2', 'img' => null, 'label' => '$2'],
                    ['denom' => '1', 'img' => null, 'label' => '$1'],
                    ['denom' => '0_5', 'img' => null, 'label' => '$0.50'],
                ] as $moneda)
                @php
                    $valor = $moneda['denom'] === '0_5' ? 0.5 : (float)$moneda['denom'];
                @endphp
                <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition-colors">
                     <div class="flex items-center gap-4 w-1/3">
                        {{-- Placeholder para imagen de moneda --}}
                        <div class="w-8 h-8 rounded-full bg-yellow-100 border border-yellow-300 flex items-center justify-center text-xs text-yellow-800 font-bold shadow-sm">
                            {{ $moneda['label'] }}
                        </div>
                    </div>

                    <div class="w-1/3 flex justify-center">
                        <input type="number" 
                               min="0" 
                               wire:model.live="monedas.{{ $moneda['denom'] }}" 
                               class="w-20 text-center border-gray-300 rounded-md focus:ring-yellow-500 focus:border-yellow-500 shadow-sm"
                               placeholder="0">
                    </div>

                    <div class="w-1/3 text-right font-medium text-gray-700">
                        ${{ number_format((float)($monedas[$moneda['denom']] ?? 0) * $valor, 2) }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Comentarios y Botón -->
    <div class="mt-8 bg-white shadow rounded-lg p-6">
        <div class="mb-4">
            <label for="comentarios" class="block text-sm font-medium text-gray-700 mb-1">Comentarios / Referencia</label>
            <textarea wire:model="comentarios" id="comentarios" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Ej. Aportación inicial de capital, Inyección de efectivo por..."></textarea>
            @error('comentarios') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end pt-4 border-t">
            <button 
                wire:click="guardar"
                wire:loading.attr="disabled"
                wire:confirm="¿Está seguro de ingresar este monto al capital?"
                class="inline-flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg wire:loading.remove xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <svg wire:loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Registrar Capital (${{ number_format($this->totalGeneral, 2) }})
            </button>
        </div>
    </div>

    <flux:modal name="success-modal" class="min-w-[22rem]" wire:model="showSuccessModal">
        <div class="space-y-6">
            <div>
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-full bg-green-100 mx-auto">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="text-center">
                    <flux:heading size="lg">¡Capital Guardado!</flux:heading>
                    <flux:subheading>El capital se ha registrado correctamente en el sistema.</flux:subheading>
                </div>
            </div>
            <div class="flex justify-center">
                <flux:button variant="primary" wire:click="$set('showSuccessModal', false)">Aceptar</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
