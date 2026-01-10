<?php

namespace App\Livewire\Caja;

use App\Models\Capitalizacion;
use Livewire\Component;

class SumarCapital extends Component
{
    public $billetes = [
        '1000' => 0,
        '500' => 0,
        '200' => 0,
        '100' => 0,
        '50' => 0,
        '20' => 0,
    ];

    public $monedas = [
        '100' => 0, // 100 pesos (moneda conmemorativa o similar si aplica, o error común, pero se incluye por si acaso)
        '20' => 0,
        '10' => 0,
        '5' => 0,
        '2' => 0,
        '1' => 0,
        '0.5' => 0,
    ];

    public $comentarios = '';
    public $showSuccessModal = false;

    public function getTotalBilletesProperty()
    {
        $total = 0;
        foreach ($this->billetes as $denom => $qty) {
            $total += (float)$denom * (int)$qty;
        }
        return $total;
    }

    public function getTotalMonedasProperty()
    {
        $total = 0;
        foreach ($this->monedas as $denom => $qty) {
            $total += (float)$denom * (int)$qty;
        }
        return $total;
    }

    public function getTotalGeneralProperty()
    {
        return $this->totalBilletes + $this->totalMonedas;
    }

    public function guardar()
    {
        $this->validate([
            'comentarios' => 'nullable|string|max:255',
        ]);

        if ($this->totalGeneral <= 0) {
            $this->dispatch('toast', message: 'El monto total debe ser mayor a 0.', type: 'error');
            return;
        }

        Capitalizacion::create([
            'monto' => $this->totalGeneral,
            'desglose_billetes' => [
                'billetes' => $this->billetes,
                'monedas' => $this->monedas,
            ],
            'user_id' => auth()->id(),
            'comentarios' => $this->comentarios,
        ]);

        $this->reset(['billetes', 'monedas', 'comentarios']);
        
        $this->showSuccessModal = true;
    }

    public function render()
    {
        return view('livewire.caja.sumar-capital');
    }
}
