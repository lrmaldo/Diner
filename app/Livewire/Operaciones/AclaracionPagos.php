<?php

namespace App\Livewire\Operaciones;

use App\Models\Prestamo;
use App\Models\Grupo;
use App\Models\Pago;
use App\Models\Holiday;
use App\Models\Configuration;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AclaracionPagos extends Component
{
    public $grupoSearch = '';
    public $prestamo = null;
    public $groupName = '';
    
    // Form Data
    public $fullPayment = false;
    public $clientData = []; // Array to store calculated data per client
    public $inputs = []; // Array to store user inputs [client_id => ['efectivo' => 0, 'moratorio' => 0]]

    public $errorMessage = '';

    public function updatedFullPayment($value)
    {
        if ($value) {
            foreach ($this->clientData as $clientId => $data) {
                // Pre-fill with correct totals directly from columns
                $this->inputs[$clientId]['efectivo'] = $data['pendiente'];
                $this->inputs[$clientId]['moratorio'] = round($data['moratorio']); 
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
        $this->reset(['prestamo', 'groupName', 'clientData', 'inputs', 'errorMessage', 'fullPayment']);

        if (empty($this->grupoSearch)) {
            return;
        }

        // Buscar directamente por ID de Préstamo (como en Pagos\Index)
        $possiblePrestamo = Prestamo::with(['grupo', 'clientes', 'pagos'])->find($this->grupoSearch);

        if (!$possiblePrestamo) {
            // Intento secundario: Si no es un número o no existe el préstamo, buscar por Nombre de Grupo (legacy support)
            // O buscar por ID de Grupo
            $grupo = Grupo::where('id', $this->grupoSearch) // Intento por ID directo del grupo
                ->orWhere('nombre', 'like', '%' . $this->grupoSearch . '%')
                ->first();
            
            if ($grupo) {
                $possiblePrestamo = Prestamo::where('grupo_id', $grupo->id)
                    ->orderBy('id', 'desc')
                    ->first();
            }
        }

        if (!$possiblePrestamo) {
            $this->errorMessage = "No se encontró un préstamo con el ID o criterio '{$this->grupoSearch}'.";
            return;
        }

        // Validar Estado
        $estadoReal = trim($possiblePrestamo->estado);
        // Permitimos 'entregado' pero también 'liquidado' si quisieras ver historial, aunque para "Aclarar Pagos" suele ser activo.
        // Pagos\Index permite 'entregado' y 'liquidado'. Aquí mantendré 'entregado' como principal requisito para pagar.
        if (strtolower($estadoReal) !== 'entregado') {
            $this->errorMessage = "El préstamo #{$possiblePrestamo->id} está en estado '{$estadoReal}'. Solo se pueden aclarar pagos de préstamos en estado 'Entregado'.";
            return;
        }

        $this->prestamo = $possiblePrestamo;
        $this->groupName = $this->prestamo->grupo 
            ? ($this->prestamo->grupo->nombre . ' (G:' . $this->prestamo->grupo->id . ' - P:' . $this->prestamo->id . ')') 
            : ('Préstamo Individual (P:' . $this->prestamo->id . ')');

        $this->prepareClientData();
    }

    private function prepareClientData()
    {
        $this->clientData = [];
        $this->inputs = [];

        // Ensure we load clients properly
        $clientes = $this->prestamo->clientes;

        // Fallback for individual loans that might not have pivot records populated correctly
        if ($clientes->isEmpty() && $this->prestamo->cliente) {
            $clientes = collect([$this->prestamo->cliente]);
        }

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
        // Safe access to pivot data (null safe operator) or fallback to loan total
        $montoAutorizado = $cliente->pivot?->monto_autorizado ?? $this->prestamo->monto_total ?? 0;
        
        $tasaInteres = $this->prestamo->tasa_interes ?? 0;
        $plazo = $this->prestamo->plazo ?? '4meses'; 
        $periodicidad = $this->prestamo->periodicidad ?? 'semanal';
        $fechaPrimerPago = $this->prestamo->fecha_primer_pago ?? now();
        
        $calendario = $this->calcularCalendarioPagos(
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
        
        // Ensure calendar is robust
        $installments = collect($calendario)->map(function($cuota) {
            return (object)$cuota;
        });
        $pagoPeriodico = $installments->first()->monto;

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

        // --- 2. Calculate Saldo Restante (Real Debt) ---
        // We use the robust model method if possible, or fallback logic
        // Formula: Total Debt - Total Paid
        $totalDebt = $installments->sum('monto');
        $saldoLiquidar = max(0, $totalDebt - $totalPagadoCapitalInteres);


        // --- 3. Calculate "Pendiente" (Same logic as Pagos/Index) ---
        // Pendiente = (Suggested Period Payment) or (Remaining Balance)
        // If we are late (bucket logic covered current installment), we ask for one full perio payment + remainder of current?
        // Pagos/Index Logic:
        // $pendiente = 0;
        // if ($saldoRestante <= 0.01) $pendiente = 0;
        // elseif ($pagoSugerido > 0) {
        //    if ($numPagoCalculado >= $totalPagosEsperados) $pendiente = $saldoRestante; 
        //    else {  calculate cents remainder... usually equals $pagoSugerido }
        // }
        // Simplified for Aclaracion: Standard Period Payment, capped at total balance.
        
        // Logic from Index.php:
        $pagoSugerido = $pagoPeriodico;
        $pendiente = 0;
        $totalPagosEsperados = count($calendario);

        if ($saldoLiquidar <= 0.01) {
            $pendiente = 0;
        } elseif ($pagoSugerido > 0) {
            if ($numeroPagoActual >= $totalPagosEsperados) {
                $pendiente = $saldoLiquidar;
            } else {
                $pagoSugeridoCentavos = (int) round($pagoSugerido * 100);
                $totalPagadoCentavos = (int) round($totalPagadoCapitalInteres * 100);
                $restoCentavos = ($pagoSugeridoCentavos > 0) ? $totalPagadoCentavos % $pagoSugeridoCentavos : 0;
                
                if ($restoCentavos == 0) {
                    $pendiente = $pagoSugerido;
                } else {
                    $pendiente = ($pagoSugeridoCentavos - $restoCentavos) / 100;
                }
                if ($pendiente > $saldoLiquidar) $pendiente = $saldoLiquidar;
            }
        }
        
        $importe = $pagoPeriodico; // "Importe" usually means the standard quota amount

        // --- 4. Calculate Moratorio (Same logic as Pagos/Index) ---
        $moratorio = 0;

        // Try model logic first
        try {
            $moratorio = $this->prestamo->calcularMoratorioVigente($cliente->id, $montoAutorizado);
        } catch (\Exception $e) {
            // Fallback logic derived from Pagos\Index.php::calcularMoratorio
            // We use calculateMoratorioLocal which replicates the exact logic of Pagos\Index
            $moratorio = $this->calculateMoratorioLocal($cliente->id, $montoAutorizado, $importe);
        }

        // Ajuste: Si el pago sugerido es a futuro (> Hoy), el pendiente (exigible) es 0.
        // Mantenemos sincronía con "Estado de Cuenta" que dice "Al día".
        if ($numeroPagoActual <= count($calendario) && $numeroPagoActual > 0) {
            $fechaCuotaStr = $calendario[$numeroPagoActual - 1]['fecha'] ?? null;
            if ($fechaCuotaStr) {
                 try {
                     $fechaCuota = Carbon::createFromFormat('d-m-y', $fechaCuotaStr)->startOfDay();
                     if ($fechaCuota->gt(now()->startOfDay())) {
                         $pendiente = 0;
                     }
                 } catch (\Exception $ex) {}
            }
        }

        return [
            'nombre' => $cliente->nombre_completo, 
            'numero_pago' => $numeroPagoActual,
            'importe' => $importe,
            'pendiente' => $pendiente,
            'saldo' => $saldoLiquidar,
            'moratorio' => $moratorio,
        ];
    }

    private function calculateMoratorioLocal($clienteId, $montoAutorizado, $cuota)
    {
        if ($cuota <= 0) return 0;

        // Re-generate calendar for this specific client to check missed payments
        $tasaInteres = $this->prestamo->tasa_interes ?? 0;
        $plazo = $this->prestamo->plazo ?? '4meses'; 
        $periodicidad = $this->prestamo->periodicidad ?? 'semanal';
        $fechaPrimerPago = $this->prestamo->fecha_primer_pago ?? now();

        $calendario = $this->calcularCalendarioPagos(
            $montoAutorizado,
            $tasaInteres,
            $plazo,
            $periodicidad,
            $fechaPrimerPago
        );

        $pagosVencidos = 0;
        $fechaHoy = now()->startOfDay();
        
        // Obtener pagos realizados por el cliente
        $pagosRealizados = $this->prestamo->pagos->where('cliente_id', $clienteId);
        
        foreach ($calendario as $pagoProg) {
            try {
                $fechaVenc = Carbon::createFromFormat('d-m-y', $pagoProg['fecha'])->startOfDay();
            } catch (\Exception $e) {
                $fechaVenc = Carbon::parse($pagoProg['fecha'])->startOfDay();
            }
            
            // Si la fecha de vencimiento ya pasó (antes de hoy)
            if ($fechaVenc->lt($fechaHoy)) {
                $montoEsperado = $pagoProg['monto'];
                
                // Buscar pagos realizados para este número de pago
                // NOTA IMPORTANTE: Pagos\Index asume que los pagos tienen 'numero_pago'. 
                // Si estamos en un sistema donde los pagos a veces pierden ese dato, esta lógica falla, 
                // pero "Estos datos son igual como el modulo de caja", así que replicamos.
                $pagadoParaEsteNumero = $pagosRealizados->where('numero_pago', $pagoProg['numero'])->sum('monto');
                
                // Si no se ha cubierto el monto esperado (con tolerancia de 1 peso)
                if ($pagadoParaEsteNumero < ($montoEsperado - 1)) {
                    $pagosVencidos++;
                }
            }
        }

        // Cargo por atraso: 5% por pago vencido
        $cargoPorAtraso = $cuota * 0.05;
        
        return $pagosVencidos * $cargoPorAtraso;
    }

    // --- Helper Methods migrated from Blade ---

    private function extraerPlazoNumerico($plazo) {
        if (is_numeric($plazo)) {
            return (int) $plazo;
        }
        preg_match('/(\d+)/', $plazo, $matches);
        return isset($matches[1]) ? (int) $matches[1] : 1;
    }

    private function calcularCalendarioPagos($monto, $tasaInteres, $plazo, $periodicidad, $fechaPrimerPago, $ultimoPago = null, $diaPago = 'martes') {
        $plazoNormalizado = strtolower(trim($plazo));
        $periodicidadNormalizada = strtolower(trim($periodicidad));
        
        $config = $this->determinarConfiguracionPago($plazoNormalizado, $periodicidadNormalizada);
        
        if (!$config) {
            return $this->calcularCalendarioBasico($monto, $tasaInteres, $plazo, $periodicidad, $fechaPrimerPago, $diaPago);
        }

        // Calcular monto total usando la misma lógica que el calendario
        $interes = (($monto / 100) * $tasaInteres) * $config['meses_interes'];
        $ivaPorcentaje = Configuration::get('iva_percentage', 16);
        $iva = ($interes / 100) * $ivaPorcentaje;
        $montoTotal = $interes + $iva + $monto;
        
        $numeroPagos = $config['total_pagos'];
        $montoPorPago = $montoTotal / $numeroPagos;

        $calendario = [];
        $fechaActual = Carbon::parse($fechaPrimerPago);

        $diasFeriados = Holiday::whereYear('date', $fechaActual->year)
            ->orWhereYear('date', $fechaActual->copy()->addYear()->year)
            ->pluck('date')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        // Determinar intervalo en días según periodicidad
        $intervaloDias = match(strtolower($periodicidadNormalizada)) {
            'semanal', 'semana', 'weekly' => 7,
            'catorcenal', 'quincenal', 'quincena', 'biweekly' => 14,
            'mensual', 'mes', 'monthly' => 30,
            default => 7
        };

        // Calcular monto por pago usando lógica de decimales (igual que en detalle de crédito y caja)
        $pagoConDecimales = $montoTotal / $numeroPagos;
        $montoPorPagoBase = floor($pagoConDecimales); // Parte entera
        $decimales = $pagoConDecimales - $montoPorPagoBase;
        $montoUltimoPago = $montoPorPagoBase + ($decimales * $numeroPagos);

        for ($i = 1; $i <= $numeroPagos; $i++) {
            if ($i === 1) {
                $fechaPago = $fechaActual->copy();
            } else {
                $fechaPago = $fechaActual->copy()->addDays($intervaloDias);
                
                while (in_array($fechaPago->format('Y-m-d'), $diasFeriados) || $fechaPago->dayOfWeek === Carbon::SUNDAY) {
                    $fechaPago->addDay();
                }
            }

            if ($i === $numeroPagos && $ultimoPago) {
                $fechaPago = Carbon::parse($ultimoPago);
            }

            // El último pago lleva el monto base más los decimales acumulados
            $montoPago = ($i === $numeroPagos) ? $montoUltimoPago : $montoPorPagoBase;

            $calendario[] = [
                'numero' => $i,
                'fecha' => $fechaPago->format('d-m-y'),
                'monto' => $montoPago,
            ];

            $fechaActual = $fechaPago->copy();
        }

        return $calendario;
    }

    private function determinarConfiguracionPago($plazo, $periodicidad) {
        $configuraciones = [
            // Caso 1: 4 meses
            '4 meses_semanal' => ['meses_interes' => 4, 'total_pagos' => 16],
            '4meses_semanal' => ['meses_interes' => 4, 'total_pagos' => 16],
            '4 meses_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 8],
            '4meses_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 8],
            '4 meses_quincenal' => ['meses_interes' => 4, 'total_pagos' => 8],
            '4meses_quincenal' => ['meses_interes' => 4, 'total_pagos' => 8],
            '16 semanas_semanal' => ['meses_interes' => 4, 'total_pagos' => 16],
            '16semanas_semanal' => ['meses_interes' => 4, 'total_pagos' => 16],
            '24 semanas_semanal' => ['meses_interes' => 6, 'total_pagos' => 24],
            '24semanas_semanal' => ['meses_interes' => 6, 'total_pagos' => 24],

            // Caso 2: 4 meses D
            '4 meses d_semanal' => ['meses_interes' => 4, 'total_pagos' => 14],
            '4meses d_semanal' => ['meses_interes' => 4, 'total_pagos' => 14],
            '4mesesd_semanal' => ['meses_interes' => 4, 'total_pagos' => 14],
            '4 meses d_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4meses d_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4mesesd_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4 meses d_quincenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4meses d_quincenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4mesesd_quincenal' => ['meses_interes' => 4, 'total_pagos' => 7],

            // Caso 3: 5 meses D
            '5 meses d_semanal' => ['meses_interes' => 5, 'total_pagos' => 18],
            '5meses d_semanal' => ['meses_interes' => 5, 'total_pagos' => 18],
            '5mesesd_semanal' => ['meses_interes' => 5, 'total_pagos' => 18],
            '5 meses d_catorcenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5meses d_catorcenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5mesesd_catorcenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5 meses d_quincenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5meses d_quincenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5mesesd_quincenal' => ['meses_interes' => 5, 'total_pagos' => 9],

            // Caso 4: 6 meses
            '6 meses_semanal' => ['meses_interes' => 6, 'total_pagos' => 24],
            '6meses_semanal' => ['meses_interes' => 6, 'total_pagos' => 24],
            '6 meses_catorcenal' => ['meses_interes' => 6, 'total_pagos' => 12],
            '6meses_catorcenal' => ['meses_interes' => 6, 'total_pagos' => 12],
            '6 meses_quincenal' => ['meses_interes' => 6, 'total_pagos' => 12],
            '6meses_quincenal' => ['meses_interes' => 6, 'total_pagos' => 12],

            // Caso 5: 1 año
            '1 año_semanal' => ['meses_interes' => 12, 'total_pagos' => 48],
            '1año_semanal' => ['meses_interes' => 12, 'total_pagos' => 48],
            '1 ano_semanal' => ['meses_interes' => 12, 'total_pagos' => 48],
            '1ano_semanal' => ['meses_interes' => 12, 'total_pagos' => 48],
            '1 año_catorcenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1año_catorcenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1 ano_catorcenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1ano_catorcenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1 año_quincenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1año_quincenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1 ano_quincenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1ano_quincenal' => ['meses_interes' => 12, 'total_pagos' => 24],
        ];

        $clave = $plazo . '_' . $periodicidad;
        return $configuraciones[$clave] ?? null;
    }

    private function calcularCalendarioBasico($monto, $tasaInteres, $plazo, $periodicidad, $fechaPrimerPago, $diaPago = 'martes') {
        $plazoNumerico = $this->extraerPlazoNumerico($plazo);
        $periodicidadNormalizada = strtolower(trim($periodicidad));
        
        $esSemanal = in_array($periodicidadNormalizada, ['semanal', 'semana', 'weekly']);
        $esQuincenal = in_array($periodicidadNormalizada, ['quincenal', 'quincena', 'biweekly']);
        $esMensual = in_array($periodicidadNormalizada, ['mensual', 'mes', 'monthly']);

        if ($esSemanal) {
            $numeroPagos = $plazoNumerico * 4;
            $intervaloDias = 7;
        } elseif ($esQuincenal) {
            $numeroPagos = $plazoNumerico * 2;
            $intervaloDias = 14;
        } elseif ($esMensual) {
            $numeroPagos = $plazoNumerico;
            $intervaloDias = 30;
        } else {
            $numeroPagos = $plazoNumerico;
            $intervaloDias = 30;
        }

        $montoTotal = $monto + ($monto * ($tasaInteres / 100));
        $montoTotal = $montoTotal + ($montoTotal * 0.16);
        $montoPorPago = $montoTotal / $numeroPagos;

        $calendario = [];
        $fechaActual = Carbon::parse($fechaPrimerPago);

        for ($i = 1; $i <= $numeroPagos; $i++) {
            $calendario[] = [
                'numero' => $i,
                'fecha' => $fechaActual->format('d-m-y'),
                'monto' => round($montoPorPago, 2),
            ];
            $fechaActual->addDays($intervaloDias);
        }

        return $calendario;
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
        $this->reset(['prestamo', 'groupName', 'clientData', 'inputs', 'errorMessage', 'fullPayment', 'grupoSearch']);
    }

    public function render()
    {
        return view('livewire.operaciones.aclaracion-pagos');
    }
}
