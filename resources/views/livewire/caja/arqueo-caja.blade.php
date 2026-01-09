<div class="p-6 max-w-7xl mx-auto" x-data="{
    fisico: {
        '1000': 0, '500': 0, '200': 0, '100': 0, '50': 0, '20': 0,
        '10': 0, '5': 0, '2': 0, '1': 0, '0.5': 0
    },
    calcularTotalFisico() {
        let total = 0;
        for (const [denom, cant] of Object.entries(this.fisico)) {
            total += parseFloat(denom) * (parseInt(cant) || 0);
        }
        return total;
    },
    formatMoney(amount) {
        return '$' + amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
}">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Arqueo de Caja</h1>
            <p class="text-sm text-gray-600">Comparativa de efectivo físico vs sistema.</p>
        </div>
        <div class="text-right">
            <span class="text-sm text-gray-500">Fecha: {{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider bg-blue-900">Denominación</th>
                    <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider bg-blue-900 w-32 border-l border-blue-800">Cantidad Física</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-white uppercase tracking-wider bg-blue-900 w-40 border-l border-blue-800">Total Físico</th>
                    <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider bg-blue-900 w-32 border-l border-blue-800">Cantidad Sistema</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-white uppercase tracking-wider bg-blue-900 w-40 border-l border-blue-800">Total Sistema</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                @php
                    $denoms = ['1000', '500', '200', '100', '50', '20', '10', '5', '2', '1', '0.5'];
                    $totalSistemaGeneral = 0;
                @endphp
                
                @foreach($denoms as $denom)
                    @php
                        $cantSistema = $denominaciones[$denom] ?? 0;
                        $totalSistema = $cantSistema * (float)$denom;
                        $totalSistemaGeneral += $totalSistema;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-2 whitespace-nowrap font-bold text-gray-700">
                            ${{ number_format((float)$denom, 2) }}
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap text-center bg-blue-50">
                            <input type="number" 
                                   min="0" 
                                   x-model.number="fisico['{{ $denom }}']" 
                                   class="w-20 text-center border-gray-300 rounded-md py-1 focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                   placeholder="0">
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap text-right font-medium text-blue-800 bg-blue-50">
                            <span x-text="formatMoney(parseFloat('{{ $denom }}') * (parseInt(fisico['{{ $denom }}']) || 0))"></span>
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap text-center text-gray-900">
                            {{ number_format($cantSistema) }}
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap text-right font-medium text-gray-900">
                            ${{ number_format($totalSistema, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 border-t-2 border-gray-300">
                <!-- Total Físico -->
                <tr>
                    <td colspan="2" class="px-6 py-3 text-right font-bold text-white bg-blue-900 uppercase">Total Efectivo Físico:</td>
                    <td class="px-6 py-3 text-right font-bold text-gray-900 text-lg">
                        <span x-text="formatMoney(calcularTotalFisico())"></span>
                    </td>
                    <td colspan="2"></td>
                </tr>
                <!-- Total Sistema -->
                <tr>
                    <td colspan="2" class="px-6 py-3 text-right font-bold text-white bg-blue-900 uppercase">Total Efectivo Sistema:</td>
                    <td class="px-6 py-3 text-right font-bold text-gray-900 text-lg">
                        ${{ number_format($this->totalSistema, 2) }}
                    </td>
                    <td colspan="2"></td>
                </tr>
                <!-- Diferencia -->
                <tr>
                    <td colspan="2" class="px-6 py-3 text-right font-bold text-white bg-blue-800 uppercase">Diferencia (-)Faltante, (+)Sobrante :</td>
                    <td class="px-6 py-3 text-right font-bold text-lg" 
                        :class="calcularTotalFisico() - {{ $this->totalSistema }} < 0 ? 'text-red-600' : (calcularTotalFisico() - {{ $this->totalSistema }} > 0 ? 'text-green-600' : 'text-gray-900')">
                        <span x-text="formatMoney(calcularTotalFisico() - {{ $this->totalSistema }})"></span>
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Botón de imprimir / guardar (Opcional, no solicitado pero útil) -->
    <div class="mt-6 flex justify-end">
        <button onclick="window.print()" class="btn-outline">
            Imprimir Reporte
        </button>
    </div>
</div>
