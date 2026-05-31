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

    public $showClientesModal = false;

    public $clientesModalTitulo = '';

    public $clientesModalRows = [];

    public $showClientesBucketModal = false;

    public $clientesBucketTitulo = '';

    public $clientesBucketRows = [];

    public function mount()
    {
        Carbon::setLocale('es');

        $this->opciones['al_dia'] = 'Al dia';

        $fecha = Carbon::now();

        for ($i = 1; $i <= 24; $i++) {
            $fechaCiclo = $fecha->copy()->subMonthsNoOverflow($i);
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
        // La vista utilizará los datos calculados
        session()->flash('message', 'Reporte generado con los parámetros seleccionados.');
    }

    public function openClientesModal(string $periodo): void
    {
        $fechaBase = $this->getBaseDate();

        if ($periodo === 'al_dia') {
            $fechaCorte = $this->parametro === 'al_dia'
                ? $fechaBase->copy()->endOfDay()
                : $fechaBase->copy()->endOfMonth()->endOfDay();
            $titulo = $this->mesesNombres['actual'];
        } elseif ($periodo === 'mes1') {
            $fechaCorte = $fechaBase->copy()->subMonthsNoOverflow(1)->endOfMonth()->endOfDay();
            $titulo = $this->mesesNombres['mes1'];
        } else {
            $fechaCorte = $fechaBase->copy()->subMonthsNoOverflow(2)->endOfMonth()->endOfDay();
            $titulo = $this->mesesNombres['mes2'];
        }

        $servicio = new \App\Services\ReportesControlService;
        $cartera = $servicio->calcularCarteraPorAsesor($fechaCorte);

        $this->clientesModalRows = $cartera['clientes_detalle'] ?? [];
        $this->clientesModalTitulo = 'Clientes de '.$titulo;
        $this->showClientesModal = true;
    }

    public function closeClientesModal(): void
    {
        $this->showClientesModal = false;
    }

    public function openClientesBucketModal(string $bucket): void
    {
        $labels = [
            'c_vigente' => 'C. vigente',
            'cv_1_7' => 'CV de 1 a 7 días',
            'cv_8_30' => 'CV de 8 a 30 días',
            'cv_31_90' => 'CV de 31 a 90 días',
            'cv_91_180' => 'CV de 91 a 180 días',
            'cv_181_365' => 'CV de 181 a 365 días',
            'cv_mas_365' => 'CV de más de 365 días',
        ];

        $this->clientesBucketRows = $this->reportesData['clientes_por_bucket'][$bucket] ?? [];
        $label = $labels[$bucket] ?? $bucket;
        $this->clientesBucketTitulo = 'Clientes en '.$label;
        $this->showClientesBucketModal = true;
    }

    public function closeClientesBucketModal(): void
    {
        $this->showClientesBucketModal = false;
    }

    // Propiedades computadas para calcular la InformaciÃ³n de las cajas (Paletas)

    // Mes actual, mes anterior y hace 2 meses
    // MÃ©todo para obtener la fecha base segÃºn el parÃ¡metro seleccionado
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
        $mesText = $this->parametro === 'al_dia' ? 'Al dia' : ucfirst($fechaBase->translatedFormat('F'));

        return [
            'actual' => $mesText,
            'mes1' => ucfirst($fechaBase->copy()->subMonthsNoOverflow(1)->translatedFormat('F')),
            'mes2' => ucfirst($fechaBase->copy()->subMonthsNoOverflow(2)->translatedFormat('F')),
        ];
    }

    #[Computed]
    public function datosClientes()
    {
        $fechaBase = $this->getBaseDate();
        $servicio = new \App\Services\ReportesControlService;

        $finMes1 = $fechaBase->copy()->subMonthsNoOverflow(1)->endOfMonth();
        $finMes2 = $fechaBase->copy()->subMonthsNoOverflow(2)->endOfMonth();

        // Para 'al_dia' usamos los datos que ya se calcularon en la tabla general para no reprocesar.
        $clientesActual = $this->datosCarteraTotales['clientes'] ?? 0;

        // Para los meses anteriores debemos calcular la cartera a esa fecha de corte
        $carteraMes1 = $servicio->calcularCarteraPorAsesor($finMes1);
        $clientesMes1 = $carteraMes1['totales']['clientes'] ?? 0;

        $carteraMes2 = $servicio->calcularCarteraPorAsesor($finMes2);
        $clientesMes2 = $carteraMes2['totales']['clientes'] ?? 0;

        return [
            'al_dia' => $clientesActual,
            'mes1' => $clientesMes1,
            'mes2' => $clientesMes2,
        ];
    }

    #[Computed]
    public function datosColocacion()
    {
        $fechaBase = $this->getBaseDate();
        $inicioActual = $fechaBase->copy()->startOfMonth();
        $finActual = $fechaBase->copy()->endOfDay();
        $inicioMes1 = $fechaBase->copy()->subMonthsNoOverflow(1)->startOfMonth();
        $finMes1 = $fechaBase->copy()->subMonthsNoOverflow(1)->endOfMonth();
        $inicioMes2 = $fechaBase->copy()->subMonthsNoOverflow(2)->startOfMonth();
        $finMes2 = $fechaBase->copy()->subMonthsNoOverflow(2)->endOfMonth();

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
    public function reportesData()
    {
        $fechaBase = $this->getBaseDate()->endOfDay();

        return (new \App\Services\ReportesControlService)->calcularCarteraPorAsesor($fechaBase);
    }

    #[Computed]
    public function datosCarteraPorAsesor()
    {
        return $this->reportesData['asesores'] ?? [];
    }

    #[Computed]
    public function datosCarteraTotales()
    {
        return $this->reportesData['totales'] ?? [];
    }

    #[Computed]
    public function datosExigible()
    {
        $fechaBase = $this->getBaseDate();
        $servicio = new \App\Services\ReportesControlService;

        // Si es "al_dia", de principio de mes a la fecha de hoy, como nos comentaron:
        if ($this->parametro === 'al_dia') {
            $inicioActual = $fechaBase->copy()->startOfMonth();
            $finActual = $fechaBase->copy()->endOfDay();
        } else {
            $inicioActual = $fechaBase->copy()->startOfMonth();
            $finActual = $fechaBase->copy()->endOfMonth()->endOfDay();
        }

        $inicioMes1 = $fechaBase->copy()->subMonthsNoOverflow(1)->startOfMonth();
        $finMes1 = $fechaBase->copy()->subMonthsNoOverflow(1)->endOfMonth()->endOfDay();

        $inicioMes2 = $fechaBase->copy()->subMonthsNoOverflow(2)->startOfMonth();
        $finMes2 = $fechaBase->copy()->subMonthsNoOverflow(2)->endOfMonth()->endOfDay();

        return [
            'al_dia' => $servicio->calcularEficienciaExigible($inicioActual, $finActual),
            'mes1' => $servicio->calcularEficienciaExigible($inicioMes1, $finMes1),
            'mes2' => $servicio->calcularEficienciaExigible($inicioMes2, $finMes2),
        ];
    }

    #[Computed]
    public function datosMontoActivo()
    {
        $fechaBase = $this->getBaseDate()->endOfDay();

        return (new \App\Services\ReportesControlService)->calcularMontoActivo($fechaBase);
    }

    #[Computed]
    public function datosFidelizacion()
    {
        $detalle = $this->datosFidelizacionDetalle;

        return [
            'al_dia' => $detalle['al_dia']['porcentaje'] ?? 0,
            'mes1' => $detalle['mes1']['porcentaje'] ?? 0,
            'mes2' => $detalle['mes2']['porcentaje'] ?? 0,
        ];
    }

    #[Computed]
    public function datosFidelizacionDetalle()
    {
        $fechaBase = $this->getBaseDate();
        $servicio = new \App\Services\ReportesControlService;

        if ($this->parametro === 'al_dia') {
            $inicioActual = $fechaBase->copy()->startOfMonth();
            $finActual = $fechaBase->copy()->endOfDay();
        } else {
            $inicioActual = $fechaBase->copy()->startOfMonth();
            $finActual = $fechaBase->copy()->endOfMonth()->endOfDay();
        }

        $inicioMes1 = $fechaBase->copy()->subMonthsNoOverflow(1)->startOfMonth();
        $finMes1 = $fechaBase->copy()->subMonthsNoOverflow(1)->endOfMonth()->endOfDay();

        $inicioMes2 = $fechaBase->copy()->subMonthsNoOverflow(2)->startOfMonth();
        $finMes2 = $fechaBase->copy()->subMonthsNoOverflow(2)->endOfMonth()->endOfDay();

        return [
            'al_dia' => $servicio->calcularFidelizacionDetalle($inicioActual, $finActual),
            'mes1' => $servicio->calcularFidelizacionDetalle($inicioMes1, $finMes1),
            'mes2' => $servicio->calcularFidelizacionDetalle($inicioMes2, $finMes2),
        ];
    }

    public function render()
    {
        return view('livewire.consultas.reportes-control');
    }
}
