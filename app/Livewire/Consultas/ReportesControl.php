<?php

namespace App\Livewire\Consultas;

use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Reportes de Control')]
class ReportesControl extends Component
{
    public $parametro = 'al_dia';

    public $opciones = [];

    public $showReport = false;

    public function mount()
    {
        Carbon::setLocale('es');

        $this->opciones['al_dia'] = 'Al dÃ­a';

        $fecha = Carbon::now();

        for ($i = 0; $i <= 24; $i++) {
            $fechaCiclo = $fecha->copy()->subMonths($i);
            $mes = $fechaCiclo->translatedFormat('F');
            $anio = $fechaCiclo->year;

            $key = $fechaCiclo->copy()->endOfMonth()->format('Y-m-d');

            $this->opciones[$key] = 'Al cierre de '.strtolower($mes).' de '.$anio;
        }
    }

    public function generar()
    {
        $this->showReport = true;
        // En este punto simplemente indicamos que se ha generado la consulta
        // La vista utilizarÃ¡ los datos calculados
        session()->flash('message', 'Reporte generado con los parÃ¡metros seleccionados.');
    }

    // Propiedades computadas para calcular la informaciÃ³n de las cajas (Paletas)

    // Mes actual, mes anterior y hace 2 meses
    // Método para obtener la fecha base según el parámetro seleccionado
    private function getBaseDate()
    {
        if ($this->parametro === 'al_dia') {
            return Carbon::now();
        }

        return Carbon::parse($this->parametro);
    }

    #[Computed]
    public function mesesNombres()
    {
        $fechaBase = $this->getBaseDate();
        $mesText = $this->parametro === 'al_dia' ? 'Al día' : ucfirst($fechaBase->translatedFormat('F'));

        return [
            'actual' => $mesText,
            'mes1' => ucfirst($fechaBase->copy()->subMonth(1)->translatedFormat('F')),
            'mes2' => ucfirst($fechaBase->copy()->subMonth(2)->translatedFormat('F')),
        ];
    }

    #[Computed]
    public function datosClientes()
    {
        $fechaBase = $this->getBaseDate();
        $finActual = $fechaBase->copy()->endOfDay();
        $finMes1 = $fechaBase->copy()->subMonth(1)->endOfMonth();
        $finMes2 = $fechaBase->copy()->subMonth(2)->endOfMonth();

        return [
            'al_dia' => \App\Models\Cliente::where('created_at', '<=', $finActual)->count(),
            'mes1' => \App\Models\Cliente::where('created_at', '<=', $finMes1)->count(),
            'mes2' => \App\Models\Cliente::where('created_at', '<=', $finMes2)->count(),
        ];
    }

    #[Computed]
    public function datosColocacion()
    {
        $fechaBase = $this->getBaseDate();
        $inicioActual = $fechaBase->copy()->startOfMonth();
        $finActual = $fechaBase->copy()->endOfDay();
        $inicioMes1 = $fechaBase->copy()->subMonth(1)->startOfMonth();
        $finMes1 = $fechaBase->copy()->subMonth(1)->endOfMonth();
        $inicioMes2 = $fechaBase->copy()->subMonth(2)->startOfMonth();
        $finMes2 = $fechaBase->copy()->subMonth(2)->endOfMonth();

        return [
            'al_dia' => \App\Models\Prestamo::where('estado', 'Entregado')
                ->whereBetween('fecha_entrega', [$inicioActual, $finActual])
                ->sum('monto_total'),

            'mes1' => \App\Models\Prestamo::where('estado', 'Entregado')
                ->whereBetween('fecha_entrega', [$inicioMes1, $finMes1])
                ->sum('monto_total'),

            'mes2' => \App\Models\Prestamo::where('estado', 'Entregado')
                ->whereBetween('fecha_entrega', [$inicioMes2, $finMes2])
                ->sum('monto_total'),
        ];
    }

    #[Computed]
    public function datosCarteraPorAsesor()
    {
        // En el futuro, aquÃ­ iteraremos sobre los asesores para sacar todos estos datos calculando saldos y dÃ­as de atraso.
        // Simulando datos para mostrar la tabla tal como se pide en la imagen:
        return [
            [
                'asesor' => 'Alejandra rivero chan',
                'c_vigente' => ['saldo' => 500000, 'clientes' => 135, 'porcentaje' => 90],
                'cv_1_7' => ['saldo' => 10000, 'clientes' => 3, 'porcentaje' => 1.7],
                'cv_8_30' => ['saldo' => 20000, 'clientes' => 5, 'porcentaje' => 3.4],
                'cv_31_90' => ['saldo' => 25000, 'clientes' => 3, 'porcentaje' => 4.3],
                'cv_91_180' => ['saldo' => 10000, 'clientes' => 2, 'porcentaje' => 1.7],
                'cv_181_365' => ['saldo' => 10000, 'clientes' => 2, 'porcentaje' => 1.7],
                'cv_mas_365' => ['saldo' => 25000, 'clientes' => 6, 'porcentaje' => 4.3],
                'cv_total' => ['saldo' => 75000, 'clientes' => 15, 'porcentaje' => 13.04],
                'creditos' => 180,
                'clientes' => 150,
                'saldo_total' => 575000,
            ],
            [
                'asesor' => 'Ãngel martin chan',
                'c_vigente' => ['saldo' => 100000, 'clientes' => 52, 'porcentaje' => 66.6],
                'cv_1_7' => ['saldo' => 10000, 'clientes' => 2, 'porcentaje' => 6.6],
                'cv_8_30' => ['saldo' => 10000, 'clientes' => 2, 'porcentaje' => 6.6],
                'cv_31_90' => ['saldo' => 10000, 'clientes' => 1, 'porcentaje' => 6.6],
                'cv_91_180' => ['saldo' => 10000, 'clientes' => 1, 'porcentaje' => 6.6],
                'cv_181_365' => ['saldo' => 10000, 'clientes' => 2, 'porcentaje' => 6.6],
                'cv_mas_365' => ['saldo' => 20000, 'clientes' => 1, 'porcentaje' => 13.3],
                'cv_total' => ['saldo' => 50000, 'clientes' => 8, 'porcentaje' => 33.3],
                'creditos' => 80,
                'clientes' => 60,
                'saldo_total' => 150000,
            ],
        ];
    }

    #[Computed]
    public function datosCarteraTotales()
    {
        // Simulando suma de datos para la fila "Totales" (fila final en rojo extraÃ­da de la imagen)
        return [
            'c_vigente' => ['saldo' => 600000, 'clientes' => 187, 'porcentaje' => 82.7],
            'cv_1_7' => ['saldo' => 20000, 'clientes' => 5, 'porcentaje' => 2.7],
            'cv_8_30' => ['saldo' => 30000, 'clientes' => 7, 'porcentaje' => 4.1],
            'cv_31_90' => ['saldo' => 35000, 'clientes' => 4, 'porcentaje' => 4.8],
            'cv_91_180' => ['saldo' => 20000, 'clientes' => 3, 'porcentaje' => 2.7],
            'cv_181_365' => ['saldo' => 20000, 'clientes' => 4, 'porcentaje' => 2.7],
            'cv_mas_365' => ['saldo' => 45000, 'clientes' => 7, 'porcentaje' => 6.2],
            'cv_total' => ['saldo' => 125000, 'clientes' => 23, 'porcentaje' => 17.2],
            'creditos' => 260,
            'clientes' => 210,
            'saldo_total' => 725000,
        ];
    }

    public function render()
    {
        return view('livewire.consultas.reportes-control');
    }
}
