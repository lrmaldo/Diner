<?php

namespace App\Livewire\Operaciones;

use App\Models\Prestamo;
use App\Models\Grupo;
use App\Models\Pago;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class AclaracionPagos extends Component
{
    public $grupoSearch = '';
    public $prestamo = null;
    public $groupName = '';
    
    // Form Data
    public $fullPayment = false;
    public $clientData = []; // Array to store calculated data per client
    public $inputs = []; // Array to store user inputs [client_id => ['efectivo' => 0, 'moratorio' => 0]]

    public $notFound = false;

    public function updatedFullPayment($value)
    {
        if ($value) {
            foreach ($this->clientData as $clientId => $data) {
                // Pre-fill with Total required (Importe + Pendiente)
                $amountToPay = $data['importe'] + $data['pendiente'];
                $this->inputs[$clientId]['efectivo'] = $amountToPay;
                // Pre-fill moratorio if we had suggested logic, for now 0 or keep existing input
                // $this->inputs[$clientId]['moratorio'] = 0; 
            }
        } else {
            foreach ($this->clientData as $clientId => $data) {
                $this->inputs[$clientId]['efectivo'] = 0;
                $this->inputs[$clientId]['moratorio'] = 0;
            }
        }
    }

    public function search()
    {
        $this->reset(['prestamo', 'groupName', 'clientData', 'inputs', 'notFound', 'fullPayment']);

        if (empty($this->grupoSearch)) {
            return;
        }

        // Search by Group ID or Name
        $grupo = Grupo::where('id', $this->grupoSearch)
            ->orWhere('nombre', 'like', '%' . $this->grupoSearch . '%')
            ->first();

        if (!$grupo) {
            $this->notFound = true;
            return;
        }

        $this->groupName = $grupo->nombre . ' (' . $grupo->id . ')';

        // Find active loan for this group
        // Prioritize 'entregado'
        $this->prestamo = Prestamo::where('grupo_id', $grupo->id)
            ->whereIn('estado', ['entregado', 'vencido']) 
            ->orderBy('id', 'desc')
            ->first();

        if (!$this->prestamo) {
            $this->notFound = true;
            return;
        }

        $this->prepareClientData();
    }

    private function prepareClientData()
    {
        $this->clientData = [];
        $this->inputs = [];

        // Ensure we load clients properly
        $clientes = $this->prestamo->clientes;

        foreach ($clientes as $cliente) {
            $stats = $this->calculateClientStats($cliente);
            $this->clientData[$cliente->id] = $stats;
            $this->inputs[$cliente->id] = [
                'efectivo' => 0,
                'moratorio' => 0
            ];
        }
    }

    private function calculateClientStats($cliente)
    {
        $montoAutorizado = $cliente->pivot->monto_autorizado ?? 0;
        
        $tasaInteres = $this->prestamo->tasa_interes ?? 0;
        $plazo = $this->prestamo->plazo ?? '4meses'; 
        $periodicidad = $this->prestamo->periodicidad ?? 'semanal';
        $fechaPrimerPago = $this->prestamo->fecha_primer_pago ?? now();
        
        $calendario = calcularCalendarioPagos(
            $montoAutorizado,
            $tasaInteres,
            $plazo,
            $periodicidad,
            $fechaPrimerPago
        );

        // Payments made by this client
        $pagosCliente = $this->prestamo->pagos->where('cliente_id', $cliente->id);
        $totalPagadoCapitalInteres = $pagosCliente->sum('monto');
        
        // --- 1. Current Installment Number ---
        // Find which installment we are currently paying based on amount paid so far
        $numeroPagoActual = 1;
        $pagadoRestante = $totalPagadoCapitalInteres;
        
        $installments = collect($calendario)->map(function($cuota) {
            return (object)$cuota;
        });

        $foundCurrent = false;
        foreach ($installments as $cuota) {
            $montoCuota = (float)$cuota->monto;
            if ($pagadoRestante >= $montoCuota - 0.01) {
                $pagadoRestante -= $montoCuota;
            } else {
                $numeroPagoActual = $cuota->numero;
                $foundCurrent = true;
                break;
            }
        }
        if (!$foundCurrent) $numeroPagoActual = count($calendario);

        // --- 2. Calculate Exigible, Pendiente, Importe ---
        // Logic: Importe = Standard Period Amount.
        // Pendiente = Total Expired Amount - Total Paid - Importe (if positive)
        
        $fechaHoy = now()->startOfDay();
        $totalExigibleHoy = 0;
        foreach ($installments as $cuota) {
            $fechaVenc = \Carbon\Carbon::createFromFormat('d-m-y', $cuota->fecha)->startOfDay();
            if ($fechaVenc <= $fechaHoy) {
                $totalExigibleHoy += $cuota->monto;
            }
        }
        
        $deudaTotalHastaHoy = max(0, $totalExigibleHoy - $totalPagadoCapitalInteres);
        
        $pagoPeriodico = $installments->first()->monto;
        
        $importe = $pagoPeriodico;
        $pendiente = max(0, $deudaTotalHastaHoy - $importe);
        
        // --- 3. Saldo to Liquidate ---
        $totalPrestamo = $installments->sum('monto');
        $saldoLiquidar = max(0, $totalPrestamo - $totalPagadoCapitalInteres);
        
        return [
            'nombre' => $cliente->nombre_completo, // Assuming accessor exists
            'numero_pago' => $numeroPagoActual,
            'importe' => $importe,
            'pendiente' => $pendiente,
            'saldo' => $saldoLiquidar,
        ];
    }

    public function aclarar()
    {
        if (!$this->prestamo) return;

        DB::transaction(function () {
            foreach ($this->inputs as $clientId => $data) {
                $monto = (float)$data['efectivo'];
                $moratorio = (float)$data['moratorio'];

                if ($monto <= 0 && $moratorio <= 0) continue;

                Pago::create([
                    'prestamo_id' => $this->prestamo->id,
                    'cliente_id' => $clientId,
                    'monto' => $monto, 
                    'moratorio_pagado' => $moratorio,
                    'fecha_pago' => now(),
                    'tipo_pago' => 'Abono', 
                    'metodo_pago' => 'banco', // Registered as Bank payment
                    'registrado_por' => auth()->id(),
                ]);
            }
        });

        session()->flash('success', 'Pagos aclarados correctamente en Banco.');
        $this->search(); // Refresh data
    }

    public function cancel()
    {
        $this->reset(['prestamo', 'groupName', 'clientData', 'inputs', 'notFound', 'fullPayment', 'grupoSearch']);
    }

    public function render()
    {
        return view('livewire.operaciones.aclaracion-pagos');
    }
}
