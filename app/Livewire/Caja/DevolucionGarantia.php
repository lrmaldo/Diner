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
    public $errorMessage = '';
    
    // Datos mostrados en la tarjeta
    public $representanteName = '';
    public $ejecutivoName = '';
    public $montoGarantiaTotal = 0;

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.caja.devolucion-garantia');
    }

    public function updatedSearch()
    {
        $this->buscarPrestamo();
    }

    public function buscarPrestamo()
    {
        $this->reset(['prestamo', 'notFound', 'errorMessage', 'representanteName', 'ejecutivoName', 'montoGarantiaTotal']);

        if (empty($this->search)) {
            return;
        }

        // Buscar por ID de préstamo / Grupo
        $this->prestamo = Prestamo::with(['cliente', 'representante', 'asesor', 'grupo', 'clientes'])
            ->find($this->search);

        if (! $this->prestamo) {
            $this->notFound = true;
            return;
        }

        // 1. Validar reglas de negocio
        
        // Estado Liquidado
        if ($this->prestamo->estado !== 'liquidado') {
            $this->errorMessage = 'El crédito no está liquidado';
        }

        // Verificar si la garantía ya fue entregada
        if ($this->prestamo->pagos()->where('tipo_pago', 'devolucion_garantia')->exists()) {
            $this->errorMessage = 'La garantía ya fue entregada.';
        }

        // 2. Cargar datos
        $this->representanteName = $this->prestamo->representante->nombre_completo ?? ($this->prestamo->cliente->nombre_completo ?? 'N/A');
        $this->ejecutivoName = $this->prestamo->asesor->name ?? 'N/A';
        
        // 3. Calcular Monto de Garantía Total y verificar multas
        $factorGarantia = ($this->prestamo->garantia ?? 10) / 100;
        
        $clientes = $this->prestamo->producto === 'grupal' 
            ? $this->prestamo->clientes 
            : ($this->prestamo->clientes->isNotEmpty() ? $this->prestamo->clientes : collect([$this->prestamo->cliente]));

        $this->montoGarantiaTotal = 0;
        $saldoMultasTotal = 0;

        foreach ($clientes as $cliente) {
            if (!$cliente) continue;
            
            // Garantía
            $montoAuth = $cliente->pivot->monto_autorizado ?? $this->prestamo->monto_total ?? 0;
            $this->montoGarantiaTotal += ($montoAuth * $factorGarantia);

            // Multas
            $detalle = $this->prestamo->calcularDetalleMoratorio($cliente->id, $montoAuth);
            $saldoMultasTotal += $detalle['saldo'];
        }

        // Si hay multas, bloqueamos la devolución
        // Usamos floor para ignorar centavos residuales (ej. $0.05) que bloquean la operación
        if (floor($saldoMultasTotal) > 0 && !$this->errorMessage) {
            $this->errorMessage = 'El préstamo tiene multas pendientes de pago ($' . number_format($saldoMultasTotal, 2) . '). No se puede devolver la garantía.';
        }
    }

    public function iniciarDevolucion()
    {
        if (!$this->prestamo || $this->errorMessage) {
            return;
        }

        // Calcular desglose para enviar a DesgloseEfectivo
        $montosDevolver = [];
        $factorGarantia = ($this->prestamo->garantia ?? 10) / 100;
        
        $clientes = $this->prestamo->producto === 'grupal' 
            ? $this->prestamo->clientes 
            : ($this->prestamo->clientes->isNotEmpty() ? $this->prestamo->clientes : collect([$this->prestamo->cliente]));
            
        $selectedClients = [];

        foreach ($clientes as $cliente) {
            if (!$cliente) continue;
             $montoAuth = $cliente->pivot->monto_autorizado ?? $this->prestamo->monto_total ?? 0;
             $garantia = round($montoAuth * $factorGarantia, 2);
             
             $montosDevolver[$cliente->id] = $garantia;
             $selectedClients[$cliente->id] = true;
        }

        $cacheKey = 'cobro_data_' . auth()->id() . '_' . $this->prestamo->id;
        \Illuminate\Support\Facades\Cache::put($cacheKey, [
            'abonos' => $montosDevolver, // Reutilizamos 'abonos' como el campo principal de monto
            'moratorios' => [],
            'selectedClients' => $selectedClients,
            'tipo_operacion' => 'devolucion', // Flag para DesgloseEfectivo
        ], now()->addMinutes(30));

        // Redirigir
        return redirect()->route('pagos.desglose-efectivo', [
            'prestamoId' => $this->prestamo->id,
            'mode' => 'devolucion'
        ]);
    }
}
