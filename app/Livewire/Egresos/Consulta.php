<?php

namespace App\Livewire\Egresos;

use App\Models\Egreso;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Consulta extends Component
{
    public $desde;
    public $hasta;

    public $egresosPorMes = [];
    public $totalGeneral = 0;
    public $generado = false;

    public function mount()
    {
        $this->desde = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->hasta = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function generar()
    {
        $this->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $egresos = Egreso::with('user')
            ->whereDate('created_at', '>=', $this->desde)
            ->whereDate('created_at', '<=', $this->hasta)
            ->orderBy('created_at')
            ->get();

        $this->egresosPorMes = $egresos
            ->groupBy(fn ($egreso) => $egreso->created_at->format('Y-m'))
            ->map(fn ($egresosMes) => [
                'etiqueta' => $egresosMes->first()->created_at->translatedFormat('F Y'),
                'egresos' => $egresosMes,
                'total' => $egresosMes->sum('monto'),
            ])
            ->sortKeys();

        $this->totalGeneral = $egresos->sum('monto');
        $this->generado = true;
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.egresos.consulta');
    }
}
