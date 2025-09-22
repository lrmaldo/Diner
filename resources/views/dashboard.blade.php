<x-layouts.app :title="__('Dashboard')">
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard — Diner (Gestión de préstamos)</h1>

            <!-- Cards métricas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Capital disponible</p>
                            <p class="text-2xl font-semibold text-project-700">$150,000</p>
                        </div>
                        <div class="bg-project-50 p-3 rounded-full">
                            <svg class="h-6 w-6 text-project-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-3 0-5 2-5 5v3h10v-3c0-3-2-5-5-5z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Clientes</p>
                            <p class="text-2xl font-semibold text-project-700">342</p>
                        </div>
                        <div class="bg-project-50 p-3 rounded-full">
                            <svg class="h-6 w-6 text-project-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1118.88 6.196M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Préstamos activos</p>
                            <p class="text-2xl font-semibold text-project-700">128</p>
                        </div>
                        <div class="bg-project-50 p-3 rounded-full">
                            <svg class="h-6 w-6 text-project-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2-2 4 4m1-10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Pagos registrados</p>
                            <p class="text-2xl font-semibold text-project-700">2,540</p>
                        </div>
                        <div class="bg-project-50 p-3 rounded-full">
                            <svg class="h-6 w-6 text-project-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow p-4 border border-gray-100">
                    <h3 class="text-lg font-medium text-gray-800 mb-3">Préstamos por mes</h3>
                    <div class="h-64" style="height:16rem; max-height:400px;">
                        <canvas id="loansChart" class="w-full h-full" style="width:100%; height:100% !important; display:block;"></canvas>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4 border border-gray-100">
                    <h3 class="text-lg font-medium text-gray-800 mb-3">Capital - 12 meses</h3>
                    <div class="h-64" style="height:16rem; max-height:400px;">
                        <canvas id="capitalChart" class="w-full h-full" style="width:100%; height:100% !important; display:block;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tabla rápida (ficticia) -->
            <div class="mt-6 bg-white rounded-lg shadow p-4 border border-gray-100">
                <h3 class="text-lg font-medium text-gray-800 mb-3">Últimos pagos registrados</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-sm text-gray-500">ID</th>
                                <th class="px-4 py-2 text-left text-sm text-gray-500">Cliente</th>
                                <th class="px-4 py-2 text-left text-sm text-gray-500">Monto</th>
                                <th class="px-4 py-2 text-left text-sm text-gray-500">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="px-4 py-3">2540</td>
                                <td class="px-4 py-3">María González</td>
                                <td class="px-4 py-3">$120</td>
                                <td class="px-4 py-3">22/09/2025</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3">2539</td>
                                <td class="px-4 py-3">José Pérez</td>
                                <td class="px-4 py-3">$80</td>
                                <td class="px-4 py-3">21/09/2025</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3">2538</td>
                                <td class="px-4 py-3">Luisa Martínez</td>
                                <td class="px-4 py-3">$200</td>
                                <td class="px-4 py-3">20/09/2025</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Datos ficticios
            const months = ['Oct', 'Nov', 'Dic', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep'];
            const loansData = [12, 18, 20, 15, 22, 30, 25, 28, 24, 26, 30, 28];
            const capitalData = [90000, 92000, 94000, 96000, 98000, 102000, 108000, 112000, 118000, 125000, 140000, 150000];

            // Loans bar chart
            if (window.loansChartInstance) { window.loansChartInstance.destroy(); }
            const ctxLoans = document.getElementById('loansChart').getContext('2d');
            window.loansChartInstance = new Chart(ctxLoans, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Préstamos nuevos',
                        data: loansData,
                        backgroundColor: 'rgba(255,39,41,0.9)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // Capital line chart
            if (window.capitalChartInstance) { window.capitalChartInstance.destroy(); }
            const ctxCapital = document.getElementById('capitalChart').getContext('2d');
            window.capitalChartInstance = new Chart(ctxCapital, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Capital disponible',
                        data: capitalData,
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
                        y: { beginAtZero: false }
                    }
                }
            });
        });
    </script>
</x-layouts.app>
