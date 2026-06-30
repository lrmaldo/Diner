<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8" x-data="{
    fisico: {
        '1000': 0, '500': 0, '200': 0, '100': 0, '50': 0, '20': 0,
        '10': 0, '5': 0, '2': 0, '1': 0, '0_5': 0
    },
    calcularTotalFisico() {
        let total = 0;
        for (const [denom, cant] of Object.entries(this.fisico)) {
            let val = denom === '0_5' ? 0.5 : parseFloat(denom);
            total += val * (parseInt(cant) || 0);
        }
        return total;
    },
    formatMoney(amount) {
        return '$' + amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
}">
    <!-- Encabezado y Acciones -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Arqueo de Caja</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Control y cuadre de efectivo físico vs sistema</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:gap-4">
            <div class="bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-100 px-4 py-1.5 rounded-lg font-bold border border-indigo-200 dark:border-indigo-700 shadow-sm text-xs sm:text-sm">
                BANCO: ${{ number_format($saldoBanco ?? 0, 2) }}
            </div>
            <span class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-medium bg-gray-100 dark:bg-zinc-700 px-3 py-1.5 rounded-full">
                {{ now()->format('d/m/Y H:i') }}
            </span>
            <flux:button variant="filled" icon="arrows-right-left" wire:click="abrirCambios" class="w-full sm:w-auto">
                Cambios
            </flux:button>
            <flux:button variant="primary" icon="plus" wire:click="abrirCapitalizar" class="w-full sm:w-auto">
                Capitalizar Caja
            </flux:button>
        </div>
    </div>

    <!-- Panel de Arqueo (Estilo Estados de Cuenta) -->
    <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <!-- Totales Superiores -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total Sistema -->
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-5 border border-blue-100 dark:border-blue-800">
                    <div class="text-sm font-medium text-blue-600 dark:text-blue-300 uppercase tracking-wider mb-1">Total en Sistema</div>
                    <div class="text-3xl font-bold text-blue-800 dark:text-blue-100">
                        ${{ number_format($this->totalSistema, 2) }}
                    </div>
                    <div class="text-xs text-blue-600/70 dark:text-blue-300/70 mt-2">
                        Calculado automáticamente
                    </div>
                </div>

                <!-- Total Físico (Input) -->
                <div class="bg-gray-50 dark:bg-zinc-700/50 rounded-xl p-5 border border-gray-200 dark:border-gray-600">
                    <div class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Total Físico (Capturado)</div>
                    <div class="text-3xl font-bold text-gray-800 dark:text-gray-100">
                        <span x-text="formatMoney(calcularTotalFisico())"></span>
                    </div>
                    <div class="text-xs text-gray-500 mt-2">
                        Ingrese cantidades abajo
                    </div>
                </div>

                <!-- Diferencia -->
                <div class="rounded-xl p-5 border transition-colors duration-300"
                     :class="calcularTotalFisico() - {{ $this->totalSistema }} == 0 
                        ? 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800' 
                        : 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800'">
                    <div class="text-sm font-medium uppercase tracking-wider mb-1"
                         :class="calcularTotalFisico() - {{ $this->totalSistema }} == 0 ? 'text-green-600 dark:text-green-300' : 'text-red-600 dark:text-red-300'">
                        Diferencia
                    </div>
                    <div class="text-3xl font-bold"
                         :class="calcularTotalFisico() - {{ $this->totalSistema }} == 0 ? 'text-green-800 dark:text-green-100' : 'text-red-800 dark:text-red-100'">
                        <span x-text="(calcularTotalFisico() - {{ $this->totalSistema }} > 0 ? '+' : '') + formatMoney(calcularTotalFisico() - {{ $this->totalSistema }})"></span>
                    </div>
                    <div class="text-xs mt-2 font-medium"
                         :class="calcularTotalFisico() - {{ $this->totalSistema }} == 0 ? 'text-green-600' : 'text-red-600'">
                        <span x-show="calcularTotalFisico() - {{ $this->totalSistema }} == 0">¡Cuadre Perfecto!</span>
                        <span x-show="calcularTotalFisico() - {{ $this->totalSistema }} != 0">Descuadre detectado</span>
                    </div>
                </div>
            </div>

            <!-- Tabla Detallada -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-zinc-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Denominación</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad Sistema</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Sistema</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider bg-blue-50/50 dark:bg-blue-900/10">Conteo Físico</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider bg-blue-50/50 dark:bg-blue-900/10">Total Físico</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $denoms = ['1000', '500', '200', '100', '50', '20', '10', '5', '2', '1', '0_5'];
                        @endphp
                        
                        @foreach($denoms as $denomKey)
                            @php
                                $cantSistema = $denominaciones[$denomKey] ?? 0;
                                $valor = $denomKey === '0_5' ? 0.5 : (float)$denomKey;
                                $totalSistema = $cantSistema * $valor;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
                                <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full {{ $valor >= 20 ? 'bg-green-500' : 'bg-yellow-500' }}"></div>
                                    ${{ number_format($valor, 2) }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-center text-sm text-gray-600 dark:text-gray-300 font-mono">
                                    {{ number_format($cantSistema) }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-right text-sm text-gray-700 dark:text-gray-300 font-medium">
                                    ${{ number_format($totalSistema, 2) }}
                                </td>
                                <td class="px-6 py-2 whitespace-nowrap text-center bg-blue-50/30 dark:bg-blue-900/5">
                                    <input type="number" 
                                           min="0" 
                                           x-model.number="fisico['{{ $denomKey }}']" 
                                           class="w-24 text-center text-sm border-gray-300 dark:border-gray-600 rounded-md py-1 focus:ring-blue-500 focus:border-blue-500 shadow-sm dark:bg-zinc-700 dark:text-gray-100"
                                           placeholder="0">
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-right text-sm font-bold text-blue-700 dark:text-blue-300 bg-blue-50/30 dark:bg-blue-900/5">
                                    <span x-text="formatMoney({{ $valor }} * (parseInt(fisico['{{ $denomKey }}']) || 0))"></span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Capitalizar -->
    <flux:modal name="capitalizar-modal" class="min-w-[50rem]" wire:model="showCapitalizarModal">
        <div class="space-y-6">
            <div class="border-b pb-4">
                <flux:heading size="lg">Capitalizar Caja</flux:heading>
                <flux:subheading>Ingrese las denominaciones para sumar capital al sistema.</flux:subheading>
            </div>

            <!-- Selección de Origen -->
            <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 mb-4">
                <flux:label class="mb-2 block font-medium">Origen de los fondos:</flux:label>
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-8">
                    <flux:radio wire:model.live="origenFondos" value="banco" label="Cuenta Diner (Banco)" />
                    <flux:radio wire:model.live="origenFondos" value="externo" label="Cuenta Externa / Aportación" />
                </div>
            </div>

            <!-- Panel Total Modal -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded-r-md">
                <div class="flex justify-between items-center">
                    <span class="text-gray-700 dark:text-gray-300 font-medium">Monto Total a Ingresar:</span>
                    <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                        ${{ number_format($this->totalGeneralCapital, 2) }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-h-[50vh] overflow-y-auto pr-2">
                <!-- Billetes -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="p-1.5 bg-green-100 rounded text-green-700">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <h3 class="font-bold text-gray-700 dark:text-gray-300">Billetes</h3>
                        <span class="ml-auto text-sm font-bold text-green-600">${{ number_format($this->totalBilletesCapital, 2) }}</span>
                    </div>
                    
                    @foreach($billetesCapital as $denom => $val)
                    <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-zinc-700/50 rounded-lg border border-gray-100 dark:border-gray-600">
                        <div class="w-20 font-bold text-green-700 dark:text-green-400 text-sm">${{ $denom }}</div>
                        <div class="flex-1 px-4">
                            <input type="number" min="0" wire:model.live="billetesCapital.{{ $denom }}" class="w-full text-center text-sm border-gray-300 rounded-md py-1 focus:ring-green-500 shadow-sm" placeholder="0">
                        </div>
                        <div class="w-20 text-right text-sm font-medium text-gray-600 dark:text-gray-400">
                            ${{ number_format((float)$denom * (int)$val, 0) }}
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Monedas -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="p-1.5 bg-yellow-100 rounded text-yellow-700">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h3 class="font-bold text-gray-700 dark:text-gray-300">Monedas</h3>
                        <span class="ml-auto text-sm font-bold text-yellow-600">${{ number_format($this->totalMonedasCapital, 2) }}</span>
                    </div>

                    @foreach($monedasCapital as $denomKey => $val)
                        @php
                            $valor = $denomKey === '0_5' ? 0.5 : (float)$denomKey;
                        @endphp
                    <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-zinc-700/50 rounded-lg border border-gray-100 dark:border-gray-600">
                        <div class="w-20 font-bold text-yellow-700 dark:text-yellow-400 text-sm">${{ $valor }}</div>
                        <div class="flex-1 px-4">
                            <input type="number" min="0" wire:model.live="monedasCapital.{{ $denomKey }}" class="w-full text-center text-sm border-gray-300 rounded-md py-1 focus:ring-yellow-500 shadow-sm" placeholder="0">
                        </div>
                        <div class="w-20 text-right text-sm font-medium text-gray-600 dark:text-gray-400">
                            ${{ number_format($valor * (int)$val, 2) }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Comentarios -->
            <div>
                <flux:label>Comentarios / Referencia</flux:label>
                <flux:textarea wire:model="comentariosCapital" placeholder="Ej. Aportación inicial de socio X..." rows="2" />
                @error('comentariosCapital') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t">
                <flux:button wire:click="$set('showCapitalizarModal', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="guardarCapital" wire:confirm="¿Está seguro de ingresar este monto al capital?">
                    Guardar Capital (${{ number_format($this->totalGeneralCapital, 2) }})
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Modal Cambios -->
    @if($showCambiosModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('showCambiosModal', false)"></div>

            <div class="relative z-10 w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-xl bg-white dark:bg-zinc-800 shadow-xl border border-gray-200 dark:border-gray-700 p-5 space-y-5">
                <div class="rounded-md px-4 py-3 text-white font-bold uppercase tracking-wide {{ $pasoCambio === 'ingresa' ? 'bg-red-600' : 'bg-red-700' }}">
                    {{ $pasoCambio === 'ingresa' ? 'Ingresa' : 'Sale' }}
                </div>

                @if($pasoCambio === 'sale')
                    <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900 dark:bg-blue-900/20 dark:border-blue-700 dark:text-blue-100">
                        Ingreso confirmado: <strong>${{ number_format($this->totalCambioEntrada, 2) }}</strong>. Ahora captura como saldra el efectivo del arqueo.
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-h-[52vh] overflow-y-auto pr-2">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-gray-700 dark:text-gray-300">Billetes</h3>
                            <span class="text-sm font-semibold text-green-700 dark:text-green-400">
                                ${{ number_format($pasoCambio === 'ingresa' ? $this->totalBilletesCambioEntrada : $this->totalBilletesCambioSalida, 2) }}
                            </span>
                        </div>

                        @if($pasoCambio === 'ingresa')
                            @foreach($billetesCambioEntrada as $denom => $cantidad)
                                <div class="flex items-center justify-between p-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-zinc-700/40">
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
                                <div class="flex items-center justify-between p-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-zinc-700/40">
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
                                    $valor = $denomKey === '0_5' ? 0.5 : (float) $denomKey;
                                    $imagen = match($denomKey) {
                                        '1' => '1peso.png',
                                        '0_5' => '50centavos.png',
                                        default => $denomKey . 'pesos.png'
                                    };
                                @endphp
                                <div class="flex items-center justify-between p-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-zinc-700/40">
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
                                    $valor = $denomKey === '0_5' ? 0.5 : (float) $denomKey;
                                    $imagen = match($denomKey) {
                                        '1' => '1peso.png',
                                        '0_5' => '50centavos.png',
                                        default => $denomKey . 'pesos.png'
                                    };
                                @endphp
                                <div class="flex items-center justify-between p-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-zinc-700/40">
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
                    <button type="button" wire:click="$set('showCambiosModal', false)" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-zinc-700">
                        Cancelar
                    </button>

                    @if($pasoCambio === 'ingresa')
                        <button type="button" wire:click="aceptarIngresoCambio" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">
                            Aceptar
                        </button>
                    @else
                        <button type="button" wire:click="guardarCambios" wire:confirm="Se aplicara el cambio al arqueo. Desea continuar?" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">
                            Cambiar
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Éxito -->
    <flux:modal name="success-modal" class="min-w-[22rem]" wire:model="showSuccessModal">
        <div class="space-y-6 text-center">
            <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 mx-auto animate-bounce">
                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div>
                <flux:heading size="lg">¡Capital Registrado!</flux:heading>
                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Has sumado <strong class="text-gray-900 dark:text-white font-bold">${{ number_format($montoGuardado, 2) }}</strong> pesos al capital.
                </div>
            </div>
            <div class="flex justify-center">
                <flux:button variant="primary" wire:click="$set('showSuccessModal', false)">Aceptar</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
