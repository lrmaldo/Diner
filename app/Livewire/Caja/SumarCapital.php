<?php

namespace App\Livewire\Caja;

use App\Models\Capitalizacion;
use Livewire\Component;

class SumarCapital extends Component
{
    public $origenFondos = 'externo'; // 'externo' | 'banco'
    public $montoDirecto = 0; // Para cuando es desde Banco

    public $billetes = [
        '1000' => 0,
        '500' => 0,
        '200' => 0,
        '100' => 0,
        '50' => 0,
        '20' => 0,
    ];

    public $monedas = [
        '100' => 0, 
        '20' => 0,
        '10' => 0,
        '5' => 0,
        '2' => 0,
        '1' => 0,
        '0_5' => 0,
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
            $val = $denom === '0_5' ? 0.5 : (float)$denom;
            $total += $val * (int)$qty;
        }
        return $total;
    }

    public function getTotalGeneralProperty()
    {
        if ($this->origenFondos === 'banco') {
            return (float)$this->montoDirecto;
        }
        return $this->totalBilletes + $this->totalMonedas;
    }

    public function updatedOrigenFondos($value)
    {
        if ($value === 'banco') {
            $this->reset(['billetes', 'monedas']);
        } else {
            $this->montoDirecto = 0;
        }
    }

    private function getSaldoBanco()
    {
        // Calculate Banco Balance
        $ingresosBanco = \App\Models\Pago::where('metodo_pago', 'banco')->sum('monto');
        $ingresosBancoMoratorio = \App\Models\Pago::where('metodo_pago', 'banco')->sum('moratorio_pagado');
        $egresosBanco = \App\Models\Capitalizacion::where('origen_fondos', 'banco')->sum('monto');
        
        return ($ingresosBanco + $ingresosBancoMoratorio) - $egresosBanco;
    }

    public function guardar()
    {
        $this->validate([
            'comentarios' => 'nullable|string|max:255',
            'montoDirecto' => 'numeric|min:0'
        ]);

        if ($this->totalGeneral <= 0) {
            $this->dispatch('toast', message: 'El monto total debe ser mayor a 0.', type: 'error');
            return;
        }

        if ($this->origenFondos === 'banco') {
            $saldoBanco = $this->getSaldoBanco();
            if ($this->totalGeneral > $saldoBanco) {
                $this->dispatch('toast', message: 'Fondos insuficientes en Cuenta Diner / Banco (' . number_format($saldoBanco) . ').', type: 'error');
                return;
            }
        }

        Capitalizacion::create([
            'monto' => $this->totalGeneral,
            'origen_fondos' => $this->origenFondos,
            // Guardar null en desglose si es de banco para no confundir con efectivo
            'desglose_billetes' => $this->origenFondos === 'externo' ? [
                'billetes' => $this->billetes,
                'monedas' => $this->monedas,
            ] : null,
            'user_id' => auth()->id(),
            'comentarios' => $this->comentarios,
        ]);

        $this->reset(['billetes', 'monedas', 'comentarios', 'montoDirecto', 'origenFondos']);
        // Default back to externo or keep selection? usually reset is safer.
        $this->origenFondos = 'externo';
        
        $this->showSuccessModal = true;
    }

    public function render()
    {
        return view('livewire.caja.sumar-capital');
    }
}
