<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Egresos</h1>
                <p class="mt-1 text-sm text-gray-500">Registro de egresos de caja o banco (sueldos, compra de insumos, etc.).</p>
            </div>
            <button wire:click="abrirModal"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Nuevo Egreso
            </button>
        </div>

        @if($showModal)
            <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl shadow-xl w-full {{ $step === 'desglose' ? 'max-w-3xl' : 'max-w-lg' }} overflow-hidden">

                    <div class="bg-red-600 px-6 py-3">
                        <h2 class="text-white text-lg font-bold">Egresos</h2>
                    </div>

                    @if($step === 'form')
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block font-semibold text-sm text-gray-800 mb-2">Origen del egreso</label>
                                <div class="flex items-center space-x-6">
                                    <label class="inline-flex items-center">
                                        <input type="radio" wire:model.live="origen" value="caja" class="form-radio text-red-600">
                                        <span class="ml-2">Caja</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" wire:model.live="origen" value="banco" class="form-radio text-red-600">
                                        <span class="ml-2">Banco</span>
                                    </label>
                                </div>
                                @error('origen') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="monto" class="block font-semibold text-sm text-gray-800 mb-1">Monto</label>
                                    <input id="monto" type="number" step="0.01" min="0.01" wire:model.live="monto"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                           placeholder="0.00">
                                    @error('monto') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="descripcion" class="block font-semibold text-sm text-gray-800 mb-1">Descripción</label>
                                    <input id="descripcion" type="text" wire:model.live="descripcion"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                           placeholder="Descripción del egreso">
                                    @error('descripcion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
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
                    @else
                        {{-- Desglose de Efectivo para egresos con origen Caja --}}
                        <div class="p-6 space-y-6">
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-3 rounded-r-md text-sm text-blue-800">
                                Selecciona las denominaciones a retirar de caja hasta cubrir exactamente
                                <span class="font-bold">${{ number_format((float) $monto, 2) }}</span>.
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                    <div class="bg-green-50 px-4 py-2 border-b border-green-100">
                                        <h3 class="text-sm font-semibold text-green-800">Billetes</h3>
                                    </div>
                                    <div class="p-2 space-y-1">
                                        @foreach(['1000', '500', '200', '100', '50', '20'] as $billete)
                                            <div class="flex items-center justify-between p-1">
                                                <span class="text-xs font-bold text-gray-500 w-12">${{ $billete }}</span>
                                                <input type="number" min="0"
                                                       wire:model.live.debounce.300ms="desgloseBilletes.{{ $billete }}"
                                                       class="block w-20 text-center rounded-md border-gray-300 text-sm py-1">
                                                <span class="text-xs text-gray-600 w-20 text-right">
                                                    ${{ number_format($billete * (float)($desgloseBilletes[$billete] ?? 0), 0) }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                    <div class="bg-yellow-50 px-4 py-2 border-b border-yellow-100">
                                        <h3 class="text-sm font-semibold text-yellow-800">Monedas</h3>
                                    </div>
                                    <div class="p-2 space-y-1">
                                        @foreach(['20', '10', '5', '2', '1', '0_5'] as $monedaKey)
                                            @php $valor = $monedaKey === '0_5' ? 0.5 : (float) $monedaKey; @endphp
                                            <div class="flex items-center justify-between p-1">
                                                <span class="text-xs font-bold text-gray-500 w-12">${{ $valor }}</span>
                                                <input type="number" min="0"
                                                       wire:model.live.debounce.300ms="desgloseMonedas.{{ $monedaKey }}"
                                                       class="block w-20 text-center rounded-md border-gray-300 text-sm py-1">
                                                <span class="text-xs text-gray-600 w-20 text-right">
                                                    ${{ number_format($valor * (float)($desgloseMonedas[$monedaKey] ?? 0), 2) }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-lg p-4 {{ abs($diferencia) < 0.01 ? 'bg-green-50 border border-green-100' : 'bg-red-50 border border-red-100' }} text-center">
                                <p class="text-sm text-gray-600">Seleccionado: <span class="font-bold">${{ number_format($totalSeleccionado, 2) }}</span> de <span class="font-bold">${{ number_format((float) $monto, 2) }}</span></p>
                                <p class="mt-1 text-sm font-semibold {{ abs($diferencia) < 0.01 ? 'text-green-700' : 'text-red-700' }}">
                                    {{ abs($diferencia) < 0.01 ? 'MONTO EXACTO' : ($diferencia > 0 ? 'Sobra $'.number_format(abs($diferencia), 2) : 'Falta $'.number_format(abs($diferencia), 2)) }}
                                </p>
                            </div>

                            <div class="flex items-center justify-end gap-3">
                                <button wire:click="cancelar" type="button"
                                        class="px-5 py-2 rounded-md font-semibold text-white bg-red-600 hover:bg-red-700">
                                    Cancelar
                                </button>
                                <button wire:click="confirmarCajaConDesglose"
                                        @if(abs($diferencia) >= 0.01) disabled @endif
                                        class="px-5 py-2 rounded-md font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed">
                                    Confirmar
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
