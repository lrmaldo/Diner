<?php

namespace App\Livewire\Egresos;

use App\Models\Egreso;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    public $showModal = false;
    public $step = 'form'; // 'form' o 'desglose'

    public $origen;
    public $monto;
    public $descripcion;
    public $fecha;

    public $desgloseBilletes = [
        '1000' => 0, '500' => 0, '200' => 0, '100' => 0, '50' => 0, '20' => 0,
    ];

    public $desgloseMonedas = [
        '20' => 0, '10' => 0, '5' => 0, '2' => 0, '1' => 0, '0_5' => 0,
    ];

    public $totalSeleccionado = 0;
    public $diferencia = 0;

    protected $rules = [
        'origen' => 'required|in:caja,banco',
        'monto' => 'required|numeric|min:0.01',
        'descripcion' => 'required|string|max:255',
        'fecha' => 'required|date|before_or_equal:today',
    ];

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.egresos.index');
    }

    public function isFormValid()
    {
        return $this->origen && is_numeric($this->monto) && $this->monto > 0 && !empty($this->descripcion) && !empty($this->fecha);
    }

    public function abrirModal()
    {
        $this->resetFields();
        $this->showModal = true;
    }

    public function cancelar()
    {
        $this->resetFields();
        $this->showModal = false;
    }

    public function resetFields()
    {
        $this->step = 'form';
        $this->origen = null;
        $this->monto = null;
        $this->descripcion = null;
        $this->fecha = Carbon::now()->format('Y-m-d');
        foreach ($this->desgloseBilletes as $k => $v) {
            $this->desgloseBilletes[$k] = 0;
        }
        foreach ($this->desgloseMonedas as $k => $v) {
            $this->desgloseMonedas[$k] = 0;
        }
        $this->totalSeleccionado = 0;
        $this->diferencia = 0;
    }

    public function confirmar()
    {
        $this->validate();

        if ($this->origen === 'caja') {
            $this->calcularTotalSeleccionado();
            $this->step = 'desglose';
            return;
        }

        $this->guardarEgreso();
        $this->dispatch('alert', type: 'success', message: 'Egreso exitoso de Banco.');
    }

    public function updatedDesgloseBilletes()
    {
        $this->calcularTotalSeleccionado();
    }

    public function updatedDesgloseMonedas()
    {
        $this->calcularTotalSeleccionado();
    }

    protected function calcularTotalSeleccionado()
    {
        $this->totalSeleccionado = 0;
        foreach ($this->desgloseBilletes as $denominacion => $cantidad) {
            $this->totalSeleccionado += (float) $denominacion * (int) $cantidad;
        }
        foreach ($this->desgloseMonedas as $denominacion => $cantidad) {
            $val = $denominacion === '0_5' ? 0.5 : (float) $denominacion;
            $this->totalSeleccionado += $val * (int) $cantidad;
        }

        $this->diferencia = round($this->totalSeleccionado - (float) $this->monto, 2);
    }

    public function confirmarCajaConDesglose()
    {
        $this->calcularTotalSeleccionado();

        if (abs($this->diferencia) > 0.01) {
            $this->dispatch('alert', type: 'error', message: 'El desglose de efectivo no coincide con el monto del egreso.');
            return;
        }

        $this->guardarEgreso([
            'billetes' => $this->desgloseBilletes,
            'monedas' => $this->desgloseMonedas,
        ]);

        $this->dispatch('alert', type: 'success', message: 'Egreso exitoso de Caja.');
    }

    protected function guardarEgreso(?array $denominaciones = null)
    {
        DB::transaction(function () use ($denominaciones) {
            Egreso::create([
                'origen' => $this->origen,
                'monto' => $this->monto,
                'descripcion' => $this->descripcion,
                'fecha' => $this->fecha,
                'denominaciones' => $denominaciones,
                'user_id' => auth()->id(),
            ]);
        });

        $this->resetFields();
        $this->showModal = false;
    }
}
