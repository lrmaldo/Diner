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

        $this->opciones['al_dia'] = 'Al dÃƒÂ­a';

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
        // La vista utilizarÃƒÂ¡ los datos calculados
        session()->flash('message', 'Reporte generado con los parÃƒÂ¡metros seleccionados.');
    }

    // Propiedades computadas para calcular la informaciÃƒÂ³n de las cajas (Paletas)

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
        $mesText = $this->parametro === 'al_dia' ? 'Al dÃ­a' : ucfirst($fechaBase->translatedFormat('F'));

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

    public function render()
    {
        return view('livewire.consultas.reportes-control');
    }
}
