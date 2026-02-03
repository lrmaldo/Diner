<?php

namespace App\Livewire\Caja;

use App\Models\Prestamo;
use Livewire\Attributes\Layout;
use Livewire\Component;

class DevolucionGarantia extends Component
{
    public $search = '';
    public $prestamo = null;
    public $notFound = false;
    
    // Arrays para el manejo de la tabla
    public $garantias = [];
    public $devueltos = [];
    public $saldos = [];
    public $montosDevolver = []; // Inputs de pago
    public $selectedClients = [];
    public $selectAll = false;

    // Totales
    public $totalDevolverInput = 0;

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.caja.devolucion-garantia');
    }

    public function updatedSearch()
    {
        $this->buscarPrestamo();
    }

    public function updatedSelectAll($value)
    {
        foreach ($this->montosDevolver as $clienteId => $monto) {
            $this->selectedClients[$clienteId] = $value;
        }
        $this->calcularTotal();
    }

    public function updatedMontosDevolver()
    {
        $this->calcularTotal();
    }
    
    public function updatedSelectedClients()
    {
        $this->calcularTotal();
    }

    public function buscarPrestamo()
    {
        $this->notFound = false;
        $this->prestamo = null;
        $this->reset(['garantias', 'devueltos', 'saldos', 'montosDevolver', 'selectedClients', 'selectAll', 'totalDevolverInput']);

        if (empty($this->search)) {
            return;
        }

        // Buscar por ID de préstamo / Grupo
        $this->prestamo = Prestamo::with(['cliente', 'representante', 'grupo', 'clientes'])
            ->find($this->search);

        if (! $this->prestamo) {
            $this->notFound = true;
            return;
        }

        // Inicializar datos para cada cliente
        $clientes = $this->prestamo->producto === 'grupal' 
            ? $this->prestamo->clientes 
            : ($this->prestamo->clientes->isNotEmpty() ? $this->prestamo->clientes : collect([$this->prestamo->cliente]));

        // Porcentaje de garantía configurado en el préstamo (default 10%)
        $porcentajeGarantia = $this->prestamo->garantia ?? 10;

        foreach ($clientes as $cliente) {
            if (!$cliente) continue;

            $montoAutorizado = 0;
            if ($this->prestamo->producto === 'grupal') {
                $montoAutorizado = $cliente->pivot->monto_autorizado ?? 0;
            } else {
                $montoAutorizado = $cliente->pivot->monto_autorizado ?? $this->prestamo->monto_total ?? 0;
            }

            // Cálculo de garantía
            $garantiaTotal = $montoAutorizado * ($porcentajeGarantia / 100);
            
            // TODO: Consultar si ya se ha devuelto algo de garantía en alguna tabla (placeholder 0)
            $devuelto = 0; 
            
            $saldo = max(0, $garantiaTotal - $devuelto);

            $this->garantias[$cliente->id] = $garantiaTotal;
            $this->devueltos[$cliente->id] = $devuelto;
            $this->saldos[$cliente->id] = $saldo;
            
            // Por defecto sugerimos devolver todo el saldo disponible
            $this->montosDevolver[$cliente->id] = $saldo;
            $this->selectedClients[$cliente->id] = false;
        }
        
        $this->calcularTotal();
    }

    public function calcularTotal()
    {
        $total = 0;
        foreach ($this->selectedClients as $id => $isSelected) {
            if ($isSelected) {
                $total += (float) ($this->montosDevolver[$id] ?? 0);
            }
        }
        $this->totalDevolverInput = $total;
    }

    public function procesarDevolucion()
    {
        // Validación básica
        if ($this->totalDevolverInput <= 0) {
            $this->dispatch('notify', type: 'error', message: 'El monto total a devolver debe ser mayor a 0');
            return;
        }

        // Lógica de guardado pendiente (placeholder)
        $this->dispatch('notify', type: 'success', message: 'Devolución procesada correctamente (Simulación)');
        
        // Resetear o recargar
        // $this->buscarPrestamo(); 
    }
}
