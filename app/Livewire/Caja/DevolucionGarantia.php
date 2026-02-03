<?php

namespace App\Livewire\Caja;

use App\Models\Prestamo;
use Livewire\Attributes\Layout;
use Livewire\Component;

class DevolucionGarantia extends Component
{
    public $modo = null; // null (menú), 'pagos' (devolución), 'multas'
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

    public function seleccionarModo($nuevoModo)
    {
        $this->modo = $nuevoModo;
        $this->reset(['search', 'prestamo', 'notFound', 'errorMessage']);
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

        // 1. Validar que esté liquidado
        if ($this->prestamo->estado !== 'liquidado') {
            $this->errorMessage = 'El crédito no está liquidado';
            $this->prestamo = null; // No mostrar datos
            return;
        }

        // 2. Cargar datos
        $this->representanteName = $this->prestamo->representante->nombre_completo ?? ($this->prestamo->cliente->nombre_completo ?? 'N/A');
        $this->ejecutivoName = $this->prestamo->asesor->name ?? 'N/A';
        
        // 3. Calcular Monto de Garantía Total
        // Sumar garantías individuales ($monto_autorizado * %garantía)
        $factorGarantia = ($this->prestamo->garantia ?? 10) / 100;
        
        $clientes = $this->prestamo->producto === 'grupal' 
            ? $this->prestamo->clientes 
            : ($this->prestamo->clientes->isNotEmpty() ? $this->prestamo->clientes : collect([$this->prestamo->cliente]));

        $totalGarantia = 0;
        foreach ($clientes as $cliente) {
            if (!$cliente) continue;
            $montoAuth = $cliente->pivot->monto_autorizado ?? $this->prestamo->monto_total ?? 0;
            $totalGarantia += ($montoAuth * $factorGarantia);
        }

        $this->montoGarantiaTotal = $totalGarantia;
    }

    public function iniciarDevolucion()
    {
        if (!$this->prestamo) return;

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
            'mode' => 'devolucion' // Query param como respaldo
        ]);
    }
}
