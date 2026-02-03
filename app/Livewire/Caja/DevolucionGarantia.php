<?php

namespace App\Livewire\Caja;

use App\Models\Prestamo;
use Livewire\Attributes\Layout;
use Livewire\Component;

class DevolucionGarantia extends Component
{
    public $modo = null; // null (selección), 'pagos' (redirige), 'multas' (pantalla actual)

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

    public function seleccionarModo($modo)
    {
        if ($modo === 'pagos') {
            return redirect()->route('pagos.index');
        }
        $this->modo = $modo;
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

            // Cálculo de Multas/Penalizaciones (Modo Multas)
            $pagosCliente = $this->prestamo->pagos->where('cliente_id', $cliente->id);
            $recuperado = $pagosCliente->sum('moratorio_pagado');
            
            // Saldo pendiente de multa visualizado a hoy
            $saldo = $this->prestamo->calcularMoratorioVigente($cliente->id, $montoAutorizado);
            
            // Penalización Total = Lo que ya pagó + Lo que debe
            $penalizacionTotal = $recuperado + $saldo;

            // Reutilizamos las variables de array:
            // garantias -> Penalización Total
            // devueltos -> Recuperado
            // saldos -> Saldo Pendiente
            $this->garantias[$cliente->id] = $penalizacionTotal;
            $this->devueltos[$cliente->id] = $recuperado;
            $this->saldos[$cliente->id] = $saldo;
            
            // Por defecto sugerimos cobrar el saldo pendiente
            $this->montosDevolver[$cliente->id] = $saldo;
            $this->selectedClients[$cliente->id] = true; // "seleccionar todo por defecto debe estar activa"
        }
        
        $this->selectAll = true;
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
        if ($this->totalDevolverInput <= 0) {
            $this->dispatch('notify', type: 'error', message: 'El monto total a cobrar debe ser mayor a 0');
            return;
        }

        // Preparar datos para DesgloseEfectivo
        $moratoriosInput = [];
        foreach ($this->selectedClients as $clienteId => $seleccionado) {
            if ($seleccionado) {
                $moratoriosInput[$clienteId] = (float) ($this->montosDevolver[$clienteId] ?? 0);
            }
        }

        $cacheKey = 'cobro_data_' . auth()->id() . '_' . $this->prestamo->id;
        \Illuminate\Support\Facades\Cache::put($cacheKey, [
            'abonos' => [], // No estamos cobrando capital/abonos aquí
            'moratorios' => $moratoriosInput, // Pasamos los montos como moratorios
            'selectedClients' => $this->selectedClients,
        ], now()->addMinutes(60));

        // Redirigir al desglose de efectivo
        return redirect()->route('pagos.desglose-efectivo', ['prestamoId' => $this->prestamo->id]);
    }
}
