<?php

namespace App\Livewire\Consultas;

use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Reportes de Control')]
class ReportesControl extends Component
{
    public $parametro = 'al_dia';

    public $opciones = [];

    public function mount()
    {
        Carbon::setLocale('es');

        $this->opciones['al_dia'] = 'Al día';

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
        // Logica para generar el reporte se implementará más adelante
        session()->flash('message', 'Generando reporte seleccionado...');
    }

    public function render()
    {
        return view('livewire.consultas.reportes-control');
    }
}
