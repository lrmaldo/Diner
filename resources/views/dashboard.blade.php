@php
    $user = Auth::user();
    
    // Datos Generales (Admin)
    $capitalDisponible = \App\Models\Configuration::get('capital_disponible', 150000);
    $totalClientes = \App\Models\Cliente::count();
    $prestamosActivos = \App\Models\Prestamo::where('estado', 'autorizado')->count();
    // Placeholder para pagos (no existe modelo Pago aún)
    $pagosRegistrados = 0; 

    // Datos para Asesor
    if ($user->isAsesor()) {
        // Clientes asignados al asesor (asumiendo relación directa o a través de préstamos)
        // Como Cliente no tiene 'asesor_id', contamos clientes con préstamos asignados a este asesor
        $misClientesCount = \App\Models\Prestamo::where('asesor_id', $user->id)
                            ->with('cliente')
                            ->get()
                            ->pluck('cliente_id')
                            ->unique()
                            ->count();
        
        $misPrestamosActivos = \App\Models\Prestamo::where('asesor_id', $user->id)
                                ->where('estado', 'autorizado')
                                ->count();
    }

    // Datos para Gráficas (Simulados basados en DB si fuera posible, aquí haremos un mix)
    // Obtener préstamos de los últimos 12 meses
    $loansData = [];
    $monthsLabels = [];
    for ($i = 11; $i >= 0; $i--) {
        $date = now()->subMonths($i);
        $monthsLabels[] = $date->format('M');
        $count = \App\Models\Prestamo::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
        $loansData[] = $count;
    }
    
    // Si es asesor, filtrar sus préstamos para la gráfica
    if ($user->isAsesor()) {
        $loansData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = \App\Models\Prestamo::where('asesor_id', $user->id)
                        ->whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->count();
            $loansData[] = $count;
        }
    }

    // Datos de Capital (Simulado/Proyectado ya que no hay histórico diario guardado simple)
    $capitalData = array_fill(0, 12, $capitalDisponible); 
@endphp

<x-layouts.app :title="__('Dashboard')">
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">
                Dashboard — Diner (Gestión de préstamos)
            </h1>

            @if($user->isAdmin())
                <!-- VISTA DE ADMINISTRADOR -->
                
                <!-- Cards métricas -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Capital -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Capital disponible</p>
                                <p class="text-2xl font-semibold text-project-700 dark:text-project-400">${{ number_format($capitalDisponible, 2) }}</p>
                            </div>
                            <div class="bg-project-50 dark:bg-project-900/50 p-3 rounded-full">
                                <svg class="h-6 w-6 text-project-700 dark:text-project-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-3 0-5 2-5 5v3h10v-3c0-3-2-5-5-5z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Clientes -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Clientes</p>
                                <p class="text-2xl font-semibold text-project-700 dark:text-project-400">{{ $totalClientes }}</p>
                            </div>
                            <div class="bg-project-50 dark:bg-project-900/50 p-3 rounded-full">
                                <svg class="h-6 w-6 text-project-700 dark:text-project-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1118.88 6.196M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Préstamos activos -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Préstamos activos</p>
                                <p class="text-2xl font-semibold text-project-700 dark:text-project-400">{{ $prestamosActivos }}</p>
                            </div>
                            <div class="bg-project-50 dark:bg-project-900/50 p-3 rounded-full">
                                <svg class="h-6 w-6 text-project-700 dark:text-project-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2-2 4 4m1-10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Pagos registrados -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Pagos registrados</p>
                                <p class="text-2xl font-semibold text-project-700 dark:text-project-400">{{ $pagosRegistrados }}</p>
                            </div>
                            <div class="bg-project-50 dark:bg-project-900/50 p-3 rounded-full">
                                <svg class="h-6 w-6 text-project-700 dark:text-project-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficas -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-100 mb-3">Préstamos por mes</h3>
                        <div class="h-64" style="height:16rem; max-height:400px;">
                            <canvas x-ref="loansChart" class="w-full h-full" style="width:100%; height:100% !important; display:block;"></canvas>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-100 mb-3">Capital - 12 meses</h3>
                        <div class="h-64" style="height:16rem; max-height:400px;">
                            <canvas x-ref="capitalChart" class="w-full h-full" style="width:100%; height:100% !important; display:block;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Tabla rápida -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-100 mb-3">Últimos pagos registrados</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-sm text-gray-500 dark:text-gray-400">ID</th>
                                    <th class="px-4 py-2 text-left text-sm text-gray-500 dark:text-gray-400">Cliente</th>
                                    <th class="px-4 py-2 text-left text-sm text-gray-500 dark:text-gray-400">Monto</th>
                                    <th class="px-4 py-2 text-left text-sm text-gray-500 dark:text-gray-400">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">No hay pagos registrados recientemente.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            @elseif($user->isAsesor())
                <!-- VISTA DE ASESOR -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <!-- Clientes -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Mis Clientes</p>
                                <p class="text-2xl font-semibold text-project-700 dark:text-project-400">{{ $misClientesCount }}</p>
                            </div>
                            <div class="bg-project-50 dark:bg-project-900/50 p-3 rounded-full">
                                <svg class="h-6 w-6 text-project-700 dark:text-project-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1118.88 6.196M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Préstamos activos -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Préstamos activos</p>
                                <p class="text-2xl font-semibold text-project-700 dark:text-project-400">{{ $misPrestamosActivos }}</p>
                            </div>
                            <div class="bg-project-50 dark:bg-project-900/50 p-3 rounded-full">
                                <svg class="h-6 w-6 text-project-700 dark:text-project-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2-2 4 4m1-10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfica de préstamos (solo esa) -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-100 dark:border-gray-700 mb-6">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-100 mb-3">Mis Préstamos por mes</h3>
                    <div class="h-64" style="height:16rem; max-height:400px;">
                        <canvas x-ref="loansChart" class="w-full h-full" style="width:100%; height:100% !important; display:block;"></canvas>
                    </div>
                </div>

            @elseif($user->isCajero())
                <!-- VISTA DE CAJERO -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <!-- Pagos registrados -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Pagos registrados hoy</p>
                                <p class="text-2xl font-semibold text-project-700 dark:text-project-400">0</p>
                            </div>
                            <div class="bg-project-50 dark:bg-project-900/50 p-3 rounded-full">
                                <svg class="h-6 w-6 text-project-700 dark:text-project-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de pagos -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-100 mb-3">Últimos pagos registrados</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-sm text-gray-500 dark:text-gray-400">ID</th>
                                    <th class="px-4 py-2 text-left text-sm text-gray-500 dark:text-gray-400">Cliente</th>
                                    <th class="px-4 py-2 text-left text-sm text-gray-500 dark:text-gray-400">Monto</th>
                                    <th class="px-4 py-2 text-left text-sm text-gray-500 dark:text-gray-400">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">No hay pagos registrados recientemente.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <!-- VISTA POR DEFECTO / OTROS ROLES -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-100 dark:border-gray-700">
                    <p class="text-gray-600 dark:text-gray-300">Bienvenido al sistema Diner.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function() {
            // Datos del servidor
            const chartData = {
                months: @json($monthsLabels),
                loansData: @json($loansData),
                capitalData: @json($capitalData)
            };

            let loansChartInstance = null;
            let capitalChartInstance = null;

            function createLoansChart() {
                const canvas = document.querySelector('[x-ref="loansChart"]');
                if (!canvas) return;

                // Asegurar que el canvas tenga un tamaño antes de crear el gráfico
                const parent = canvas.parentElement;
                if (!parent || parent.offsetWidth === 0 || parent.offsetHeight === 0) {
                    setTimeout(createLoansChart, 100);
                    return;
                }

                // Destruir instancia anterior si existe
                if (loansChartInstance) {
                    loansChartInstance.destroy();
                }

                const ctx = canvas.getContext('2d');
                loansChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartData.months,
                        datasets: [{
                            label: 'Préstamos nuevos',
                            data: chartData.loansData,
                            backgroundColor: 'rgba(255,39,41,0.9)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { 
                                beginAtZero: true,
                                grid: { color: 'rgba(156, 163, 175, 0.1)' },
                                ticks: { color: '#9CA3AF' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#9CA3AF' }
                            }
                        },
                        plugins: {
                            legend: { labels: { color: '#9CA3AF' } }
                        }
                    }
                });
            }

            function createCapitalChart() {
                const canvas = document.querySelector('[x-ref="capitalChart"]');
                if (!canvas) return;

                // Asegurar que el canvas tenga un tamaño antes de crear el gráfico
                const parent = canvas.parentElement;
                if (!parent || parent.offsetWidth === 0 || parent.offsetHeight === 0) {
                    setTimeout(createCapitalChart, 100);
                    return;
                }

                // Destruir instancia anterior si existe
                if (capitalChartInstance) {
                    capitalChartInstance.destroy();
                }

                const ctx = canvas.getContext('2d');
                capitalChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.months,
                        datasets: [{
                            label: 'Capital disponible',
                            data: chartData.capitalData,
                            borderColor: 'rgba(255,39,41,1)',
                            backgroundColor: 'rgba(255,39,41,0.12)',
                            fill: true,
                            tension: 0.3,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { 
                                beginAtZero: false,
                                grid: { color: 'rgba(156, 163, 175, 0.1)' },
                                ticks: { color: '#9CA3AF' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#9CA3AF' }
                            }
                        },
                        plugins: {
                            legend: { labels: { color: '#9CA3AF' } }
                        }
                    }
                });
            }

            function initCharts() {
                if (typeof Chart === 'undefined') {
                    setTimeout(initCharts, 100);
                    return;
                }

                // Esperar a que el DOM esté completamente listo
                setTimeout(() => {
                    createLoansChart();
                    createCapitalChart();
                }, 200);
            }

            // Inicializar cuando el DOM esté listo
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initCharts);
            } else {
                initCharts();
            }

            // Reinicializar en navegación de Livewire
            document.addEventListener('livewire:navigated', initCharts);
        })();
    </script>
</x-layouts.app>

