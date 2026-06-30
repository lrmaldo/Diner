<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-8 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-red-100 text-2xl">
                    💸
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Egresos</h1>
                    <p class="mt-1 text-sm text-gray-500">Registro de egresos de caja o banco (sueldos, compra de insumos, etc.).</p>
                </div>
            </div>
            @if(!$showModal)
                <button wire:click="abrirModal"
                        class="inline-flex items-center gap-2 px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span class="text-base">➕</span> Nuevo Egreso
                </button>
            @endif
        </div>

        @if(!$showModal)
            {{-- Estado vacío --}}
            <div class="flex justify-center">
                <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-gray-200 p-10 text-center">
                    <div class="mx-auto mb-4 flex items-center justify-center h-16 w-16 rounded-full bg-gradient-to-br from-red-100 to-orange-100 text-3xl">
                        🧾
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Sin egresos en proceso</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Registra sueldos, compra de insumos u otros gastos que salgan de Caja o Banco.
                    </p>
                    <button wire:click="abrirModal"
                            class="mt-6 inline-flex items-center gap-2 px-5 py-2.5 rounded-md font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm">
                        <span class="text-base">➕</span> Registrar un Egreso
                    </button>
                </div>
            </div>
        @endif

        @if($showModal && $step === 'form')
            {{-- Formulario de registro de Egreso --}}
            <div class="flex justify-center">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 w-full max-w-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-red-600 to-red-500 px-6 py-4">
                        <h2 class="text-white text-lg font-bold flex items-center gap-2">
                            <span class="text-xl">💸</span> Nuevo Egreso
                        </h2>
                    </div>

                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block font-semibold text-sm text-gray-800 mb-2">Origen del egreso</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center justify-center gap-2 rounded-lg border-2 p-3 cursor-pointer transition-colors {{ $origen === 'caja' ? 'border-red-500 bg-red-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <input type="radio" wire:model.live="origen" value="caja" class="form-radio text-red-600">
                                    <span class="text-lg">🗄️</span>
                                    <span class="font-medium">Caja</span>
                                </label>
                                <label class="flex items-center justify-center gap-2 rounded-lg border-2 p-3 cursor-pointer transition-colors {{ $origen === 'banco' ? 'border-red-500 bg-red-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <input type="radio" wire:model.live="origen" value="banco" class="form-radio text-red-600">
                                    <span class="text-lg">🏦</span>
                                    <span class="font-medium">Banco</span>
                                </label>
                            </div>
                            @error('origen') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="monto" class="block font-semibold text-sm text-gray-800 mb-1">Monto</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">$</span>
                                    <input id="monto" type="number" step="0.01" min="0.01" wire:model.live="monto"
                                           class="block w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                           placeholder="0.00">
                                </div>
                                @error('monto') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="fecha" class="block font-semibold text-sm text-gray-800 mb-1">Fecha</label>
                                <input id="fecha" type="date" max="{{ now()->format('Y-m-d') }}" wire:model.live="fecha"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                @error('fecha') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="descripcion" class="block font-semibold text-sm text-gray-800 mb-1">Descripción</label>
                            <input id="descripcion" type="text" wire:model.live="descripcion"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                   placeholder="Descripción del egreso">
                            @error('descripcion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button wire:click="cancelar" type="button"
                                    class="px-5 py-2 rounded-md font-semibold text-white bg-red-600 hover:bg-red-700">
                                Cancelar
                            </button>

                            @if($origen === 'banco')
                                <button wire:click="confirmar"
                                        wire:confirm="¿Confirmar retiro de ${{ number_format((float)($monto ?? 0), 2) }} de la cuenta bancaria?"
                                        @disabled(!$this->isFormValid())
                                        class="px-5 py-2 rounded-md font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed">
                                    Confirmar
                                </button>
                            @else
                                <button wire:click="confirmar"
                                        @disabled(!$this->isFormValid())
                                        class="px-5 py-2 rounded-md font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed">
                                    Confirmar
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @elseif($showModal && $step === 'desglose')
            {{-- Desglose de Efectivo para egresos con origen Caja --}}
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Desglose de Efectivo</h2>
                    <p class="text-sm text-gray-500">Egreso: {{ $descripcion }}</p>
                </div>
                <button wire:click="cancelar" type="button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancelar
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                <div class="lg:col-span-8 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Billetes --}}
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-green-50 px-4 py-2 border-b border-green-100 flex items-center justify-between">
                                <h2 class="text-base font-semibold text-green-800 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Billetes
                                </h2>
                                <span class="text-xs font-medium text-green-600 bg-green-100 px-2 py-0.5 rounded-full">
                                    ${{ number_format(collect($desgloseBilletes)->map(fn($cant, $val) => (float)$cant * $val)->sum(), 2) }}
                                </span>
                            </div>
                            <div class="p-2 space-y-1">
                                @foreach(['1000', '500', '200', '100', '50', '20'] as $billete)
                                    <div class="flex items-center justify-between p-1 hover:bg-gray-50 rounded border border-transparent hover:border-gray-100 transition-colors">
                                        <div class="flex items-center gap-2 w-1/3">
                                            <img src="{{ asset('img/billetes-monedas/billetes/' . $billete . 'pesos.png') }}"
                                                 alt="${{ $billete }}"
                                                 class="h-8 w-auto object-contain shadow-sm rounded-sm">
                                            <span class="text-xs font-bold text-gray-500 hidden sm:inline">${{ $billete }}</span>
                                        </div>
                                        <div class="w-1/3 px-2">
                                            <input type="number"
                                                   min="0"
                                                   wire:model.live.debounce.500ms="desgloseBilletes.{{ $billete }}"
                                                   class="block w-full text-center border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 text-sm font-semibold py-1"
                                                   placeholder="0">
                                        </div>
                                        <div class="w-1/3 text-right text-xs text-gray-600 font-medium">
                                            ${{ number_format($billete * (float)($desgloseBilletes[$billete] ?? 0), 0) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Monedas --}}
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-yellow-50 px-4 py-2 border-b border-yellow-100 flex items-center justify-between">
                                <h2 class="text-base font-semibold text-yellow-800 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Monedas
                                </h2>
                                <span class="text-xs font-medium text-yellow-600 bg-yellow-100 px-2 py-0.5 rounded-full">
                                    ${{ number_format(collect($desgloseMonedas)->map(fn($cant, $val) => (float)$cant * ($val === '0_5' ? 0.5 : $val))->sum(), 2) }}
                                </span>
                            </div>
                            <div class="p-2 space-y-1">
                                @foreach(['20', '10', '5', '2', '1', '0_5'] as $monedaKey)
                                    @php
                                        $monedaKeyStr = (string) $monedaKey;
                                        $valor = $monedaKeyStr === '0_5' ? 0.5 : (float) $monedaKeyStr;
                                        $imagen = match($monedaKeyStr) {
                                            '1' => '1peso.png',
                                            '0_5' => '50centavos.png',
                                            default => $monedaKeyStr . 'pesos.png'
                                        };
                                    @endphp
                                    <div class="flex items-center justify-between p-1 hover:bg-gray-50 rounded border border-transparent hover:border-gray-100 transition-colors">
                                        <div class="flex items-center gap-2 w-1/3">
                                            <img src="{{ asset('img/billetes-monedas/monedas/' . $imagen) }}"
                                                 alt="${{ $valor }}"
                                                 class="h-8 w-8 object-contain rounded-full">
                                            <span class="text-xs font-bold text-gray-500 hidden sm:inline">${{ $valor }}</span>
                                        </div>
                                        <div class="w-1/3 px-2">
                                            <input type="number"
                                                   min="0"
                                                   wire:model.live.debounce.500ms="desgloseMonedas.{{ $monedaKey }}"
                                                   class="block w-full text-center border-gray-300 rounded-md focus:ring-yellow-500 focus:border-yellow-500 text-sm font-semibold py-1"
                                                   placeholder="0">
                                        </div>
                                        <div class="w-1/3 text-right text-xs text-gray-600 font-medium">
                                            ${{ number_format($valor * (float)($desgloseMonedas[$monedaKey] ?? 0), 2) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Resumen --}}
                <div class="lg:col-span-4 space-y-4">
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden sticky top-6">
                        <div class="bg-gray-900 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">Resumen del Egreso</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                                <span class="text-gray-600">Monto del Egreso</span>
                                <span class="text-2xl font-bold text-gray-900">${{ number_format((float) $monto, 2) }}</span>
                            </div>

                            <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                                <span class="text-gray-600">Efectivo Seleccionado</span>
                                <span class="text-2xl font-bold text-blue-600">${{ number_format($totalSeleccionado, 2) }}</span>
                            </div>

                            <div class="rounded-lg p-4 {{ abs($diferencia) < 0.01 ? 'bg-green-50 border border-green-100' : 'bg-red-50 border border-red-100' }}">
                                <div class="text-center">
                                    <span class="block text-sm font-medium {{ abs($diferencia) < 0.01 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ abs($diferencia) < 0.01 ? 'MONTO EXACTO' : ($diferencia > 0 ? 'SOBRA DINERO' : 'FALTA DINERO') }}
                                    </span>
                                    @if(abs($diferencia) >= 0.01)
                                        <span class="block text-xl font-extrabold mt-1 text-red-700">
                                            Diferencia: ${{ number_format(abs($diferencia), 2) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <button wire:click="confirmarCajaConDesglose"
                                    wire:loading.attr="disabled"
                                    wire:target="confirmarCajaConDesglose"
                                    @if(abs($diferencia) >= 0.01) disabled @endif
                                    class="w-full flex justify-center py-4 px-4 border border-transparent rounded-lg shadow-sm text-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <span wire:loading.remove wire:target="confirmarCajaConDesglose">Confirmar Egreso</span>
                                <span wire:loading wire:target="confirmarCajaConDesglose">Procesando...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
