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
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Arqueo de Caja</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Control y cuadre de efectivo físico vs sistema</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-100 px-4 py-1 rounded-lg font-bold border border-indigo-200 dark:border-indigo-700 shadow-sm text-sm">
                BANCO: ${{ number_format($saldoBanco ?? 0, 2) }}
            </div>
            <span class="text-sm text-gray-500 dark:text-gray-400 font-medium bg-gray-100 dark:bg-zinc-700 px-3 py-1 rounded-full">
                {{ now()->format('d/m/Y H:i') }}
            </span>
            <flux:button variant="primary" icon="plus" wire:click="abrirCapitalizar">
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
            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
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
