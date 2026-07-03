<div class="p-4 sm:p-6 max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Cambio de denominaciones</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Registra el efectivo que entra a caja (INGRESA) y el que sale (SALE) al hacer un cambio de billetes o monedas.
        </p>
    </div>

    <div class="rounded-xl bg-white dark:bg-zinc-800 shadow-sm border border-gray-200 dark:border-gray-700 p-5 space-y-5">
        <div class="rounded-md px-4 py-3 text-white font-bold uppercase tracking-wide {{ $pasoCambio === 'ingresa' ? 'bg-red-600' : 'bg-red-700' }}">
            {{ $pasoCambio === 'ingresa' ? 'Ingresa' : 'Sale' }}
        </div>

        @if($pasoCambio === 'sale')
            <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900 dark:bg-blue-900/20 dark:border-blue-700 dark:text-blue-100">
                Ingreso confirmado: <strong>${{ number_format($this->totalCambioEntrada, 2) }}</strong>. Ahora captura como saldra el efectivo del arqueo. El monto que sale debe ser <strong>exactamente igual</strong> al que ingresó.
            </div>

            {{-- Comparativa en vivo: no se permite dar de menos ni de más --}}
            <div class="rounded-lg border p-3 flex flex-wrap items-center justify-between gap-2
                {{ $this->montosCuadran ? 'border-green-300 bg-green-50 dark:bg-green-900/20 dark:border-green-700' : 'border-amber-300 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700' }}">
                <div class="text-sm">
                    <span class="text-gray-600 dark:text-gray-300">Ingresó:</span>
                    <strong class="text-gray-900 dark:text-gray-100">${{ number_format($this->totalCambioEntrada, 2) }}</strong>
                    <span class="mx-2 text-gray-400">|</span>
                    <span class="text-gray-600 dark:text-gray-300">Sale:</span>
                    <strong class="text-gray-900 dark:text-gray-100">${{ number_format($this->totalCambioSalida, 2) }}</strong>
                </div>
                @if($this->montosCuadran)
                    <span class="text-sm font-bold text-green-700 dark:text-green-400">✓ Los montos coinciden</span>
                @elseif($this->diferenciaCambio < 0)
                    <span class="text-sm font-bold text-amber-700 dark:text-amber-400">Faltan ${{ number_format(abs($this->diferenciaCambio), 2) }}</span>
                @else
                    <span class="text-sm font-bold text-red-700 dark:text-red-400">Sobran ${{ number_format($this->diferenciaCambio, 2) }}</span>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-gray-700 dark:text-gray-300">Billetes</h3>
                    <span class="text-sm font-semibold text-green-700 dark:text-green-400">
                        ${{ number_format($pasoCambio === 'ingresa' ? $this->totalBilletesCambioEntrada : $this->totalBilletesCambioSalida, 2) }}
                    </span>
                </div>

                @if($pasoCambio === 'ingresa')
                    @foreach($billetesCambioEntrada as $denom => $cantidad)
                        <div wire:key="cambio-ingresa-billete-{{ $denom }}" class="flex items-center justify-between p-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-zinc-700/40">
                            <div class="w-20 flex items-center gap-2">
                                <img src="{{ asset('img/billetes-monedas/billetes/' . $denom . 'pesos.png') }}" alt="Billete ${{ $denom }}" class="h-7 w-auto object-contain rounded-sm shadow-sm">
                                <span class="text-xs font-bold text-green-700 dark:text-green-400">${{ $denom }}</span>
                            </div>
                            <div class="flex-1 px-4">
                                <input type="number" min="0" wire:model.live.debounce.250ms="billetesCambioEntrada.{{ $denom }}" class="w-full text-center text-sm border-gray-300 rounded-md py-1 focus:ring-green-500 shadow-sm dark:bg-zinc-700 dark:text-gray-100" placeholder="0">
                            </div>
                            <div class="w-20 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                                ${{ number_format((float) $denom * (int) ($billetesCambioEntrada[$denom] ?? 0), 0) }}
                            </div>
                        </div>
                    @endforeach
                @else
                    @foreach($billetesCambioSalida as $denom => $cantidad)
                        <div wire:key="cambio-sale-billete-{{ $denom }}" class="flex items-center justify-between p-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-zinc-700/40">
                            <div class="w-20 flex items-center gap-2">
                                <img src="{{ asset('img/billetes-monedas/billetes/' . $denom . 'pesos.png') }}" alt="Billete ${{ $denom }}" class="h-7 w-auto object-contain rounded-sm shadow-sm">
                                <span class="text-xs font-bold text-green-700 dark:text-green-400">${{ $denom }}</span>
                            </div>
                            <div class="flex-1 px-4">
                                <input type="number" min="0" wire:model.live.debounce.250ms="billetesCambioSalida.{{ $denom }}" class="w-full text-center text-sm border-gray-300 rounded-md py-1 focus:ring-green-500 shadow-sm dark:bg-zinc-700 dark:text-gray-100" placeholder="0">
                            </div>
                            <div class="w-20 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                                ${{ number_format((float) $denom * (int) ($billetesCambioSalida[$denom] ?? 0), 0) }}
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-gray-700 dark:text-gray-300">Monedas</h3>
                    <span class="text-sm font-semibold text-yellow-700 dark:text-yellow-400">
                        ${{ number_format($pasoCambio === 'ingresa' ? $this->totalMonedasCambioEntrada : $this->totalMonedasCambioSalida, 2) }}
                    </span>
                </div>

                @if($pasoCambio === 'ingresa')
                    @foreach($monedasCambioEntrada as $denomKey => $cantidad)
                        @php
                            $denomKeyStr = (string) $denomKey;
                            $valor = $denomKeyStr === '0_5' ? 0.5 : (float) $denomKeyStr;
                            $imagen = match($denomKeyStr) {
                                '1' => '1peso.png',
                                '0_5' => '50centavos.png',
                                default => $denomKeyStr . 'pesos.png'
                            };
                        @endphp
                        <div wire:key="cambio-ingresa-moneda-{{ $denomKey }}" class="flex items-center justify-between p-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-zinc-700/40">
                            <div class="w-20 flex items-center gap-2">
                                <img src="{{ asset('img/billetes-monedas/monedas/' . $imagen) }}" alt="Moneda ${{ $valor }}" class="h-7 w-7 object-contain rounded-full">
                                <span class="text-xs font-bold text-yellow-700 dark:text-yellow-400">${{ $valor }}</span>
                            </div>
                            <div class="flex-1 px-4">
                                <input type="number" min="0" wire:model.live.debounce.250ms="monedasCambioEntrada.{{ $denomKey }}" class="w-full text-center text-sm border-gray-300 rounded-md py-1 focus:ring-yellow-500 shadow-sm dark:bg-zinc-700 dark:text-gray-100" placeholder="0">
                            </div>
                            <div class="w-20 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                                ${{ number_format($valor * (int) ($monedasCambioEntrada[$denomKey] ?? 0), 2) }}
                            </div>
                        </div>
                    @endforeach
                @else
                    @foreach($monedasCambioSalida as $denomKey => $cantidad)
                        @php
                            $denomKeyStr = (string) $denomKey;
                            $valor = $denomKeyStr === '0_5' ? 0.5 : (float) $denomKeyStr;
                            $imagen = match($denomKeyStr) {
                                '1' => '1peso.png',
                                '0_5' => '50centavos.png',
                                default => $denomKeyStr . 'pesos.png'
                            };
                        @endphp
                        <div wire:key="cambio-sale-moneda-{{ $denomKey }}" class="flex items-center justify-between p-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-zinc-700/40">
                            <div class="w-20 flex items-center gap-2">
                                <img src="{{ asset('img/billetes-monedas/monedas/' . $imagen) }}" alt="Moneda ${{ $valor }}" class="h-7 w-7 object-contain rounded-full">
                                <span class="text-xs font-bold text-yellow-700 dark:text-yellow-400">${{ $valor }}</span>
                            </div>
                            <div class="flex-1 px-4">
                                <input type="number" min="0" wire:model.live.debounce.250ms="monedasCambioSalida.{{ $denomKey }}" class="w-full text-center text-sm border-gray-300 rounded-md py-1 focus:ring-yellow-500 shadow-sm dark:bg-zinc-700 dark:text-gray-100" placeholder="0">
                            </div>
                            <div class="w-20 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                                ${{ number_format($valor * (int) ($monedasCambioSalida[$denomKey] ?? 0), 2) }}
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Referencia (opcional)</label>
            <textarea wire:model="comentariosCambio" rows="2" placeholder="Ej. Cambio de billetes para caja chica" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-zinc-700 dark:text-gray-100"></textarea>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 flex items-center justify-between">
            <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Total {{ $pasoCambio === 'ingresa' ? 'Ingresa' : 'Sale' }}</span>
            <span class="text-xl font-bold {{ $pasoCambio === 'ingresa' ? 'text-green-700 dark:text-green-400' : 'text-blue-700 dark:text-blue-400' }}">
                ${{ number_format($pasoCambio === 'ingresa' ? $this->totalCambioEntrada : $this->totalCambioSalida, 2) }}
            </span>
        </div>

        <div class="flex justify-end gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
            @if($pasoCambio === 'ingresa')
                <button type="button" wire:click="resetCambioForm" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-zinc-700">
                    Cancelar
                </button>
                <button type="button" wire:click="aceptarIngresoCambio" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">
                    Aceptar
                </button>
            @else
                <button type="button" wire:click="resetCambioForm" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-zinc-700">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarCambios" wire:confirm="Se aplicara el cambio al arqueo. Desea continuar?"
                        @disabled(! $this->montosCuadran)
                        class="px-4 py-2 rounded-md text-white {{ $this->montosCuadran ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed' }}">
                    Cambiar
                </button>
            @endif
        </div>
    </div>
</div>
