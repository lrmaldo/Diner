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

        $this->opciones['al_dia'] = 'Al dia';

        $fecha = Carbon::now();

        for ($i = 0; $i <= 24; $i++) {
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
            'al_dia' => $servicio->calcularFidelizacion($inicioActual, $finActual),
            'mes1' => $servicio->calcularFidelizacion($inicioMes1, $finMes1),
            'mes2' => $servicio->calcularFidelizacion($inicioMes2, $finMes2),
        ];
    }

    public function render()
    {
        return view('livewire.consultas.reportes-control');
    }
}
