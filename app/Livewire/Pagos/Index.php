<?php

namespace App\Livewire\Pagos;

use App\Models\Prestamo;
use App\Models\Holiday;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    public $modo = null; // 'pagos' o 'multas'

    public $search = '';

    public $prestamo = null;

    public $notFound = false;

    public $abonos = [];
    public $pendientes = [];
    public $moratorios = [];
    public $saldosRestantes = [];
    public $siguientesPagos = [];
    public $selectedClients = [];
    public $selectAll = false;

    public function seleccionarModo($modo)
    {
        $this->modo = $modo;
        $this->reset(['search', 'prestamo', 'notFound']);
    }

    public function updatedSelectAll($value)
    {
        foreach ($this->selectedClients as $key => $val) {
            $this->selectedClients[$key] = $value;
        }
    }

    public function updatedSearch()
    {
        $this->buscarPrestamo();
    }

    public function buscarPrestamo()
    {
        $this->notFound = false;
        $this->prestamo = null;
        $this->abonos = [];
        $this->pendientes = [];
        $this->moratorios = [];
        $this->saldosRestantes = [];
        $this->siguientesPagos = [];
        $this->selectedClients = [];
        $this->selectAll = false;

        if (empty($this->search)) {
            return;
        }

        // Buscar por ID de préstamo
        $this->prestamo = Prestamo::with(['cliente', 'representante', 'asesor', 'grupo', 'clientes', 'pagos'])
            ->find($this->search);

        if (! $this->prestamo) {
            $this->notFound = true;
        } else {
            // Validar estado del préstamo (Solo se puede cobrar si está entregado o liquidado)
            if (!in_array($this->prestamo->estado, ['entregado', 'liquidado'])) {
                return;
            }

            // Inicializar abonos y selección
            $clientes = $this->prestamo->producto === 'grupal' 
                ? $this->prestamo->clientes 
                : ($this->prestamo->clientes->isNotEmpty() ? $this->prestamo->clientes : collect([$this->prestamo->cliente]));

            // Filtrar clientes nulos para evitar errores
            $clientes = $clientes->filter(function ($value) {
                return !is_null($value);
            });

            foreach ($clientes as $cliente) {
                $montoAutorizado = 0;
                if ($this->prestamo->producto === 'grupal') {
                    $montoAutorizado = $cliente->pivot->monto_autorizado ?? 0;
                } else {
                    $montoAutorizado = $cliente->pivot->monto_autorizado ?? $this->prestamo->monto_total ?? 0;
                }

                $pagoSugerido = $this->calcularCuota($montoAutorizado);
                
                // Calcular Total Adeudo (Total Debt)
                $totalAdeudo = $this->calcularTotalAdeudo($montoAutorizado);

                // Calcular Pendiente
                $pagosCliente = $this->prestamo->pagos->where('cliente_id', $cliente->id);
                $totalPagado = $pagosCliente->sum('monto');
                
                // Calcular Saldo Restante (Deuda Total para Liquidar)
                try {
                    $saldoRestante = $this->prestamo->calcularSaldoLiquidarParaCliente($cliente->id, $montoAutorizado);
                } catch (\Exception $e) {
                    $saldoRestante = floor(max(0, $totalAdeudo - $totalPagado));
                }
                
                $this->saldosRestantes[$cliente->id] = $saldoRestante;

                // Calcular siguiente número de pago basado en monto pagado acumulado (Bucket Logic)
                $numPagoCalculado = 1;
                $totalPagosEsperados = 1000; // Valor seguro por defecto

                if ($pagoSugerido > 0) {
                    $pagosAcumulados = $totalPagado;
                    // Cuántos pagos enteros caben en lo pagado
                    $pagosCubiertos = floor($pagosAcumulados / $pagoSugerido);
                    $numPagoCalculado = $pagosCubiertos + 1;
                    
                    // Asegurar que no exceda el total de pagos + 1 (o mantener en total si ya acabó)
                    $plazo = strtolower(trim($this->prestamo->plazo));
                    $periodicidad = strtolower(trim($this->prestamo->periodicidad));
                    $config = $this->determinarConfiguracionPago($plazo, $periodicidad);
                    $totalPagosEsperados = $config['total_pagos'] ?? 16;
                    
                    if ($numPagoCalculado > $totalPagosEsperados) {
                        $numPagoCalculado = $totalPagosEsperados + 1; // O indicar terminado
                        
                        // Si ya está liquidado (saldo 0), a veces se prefiere mostrar el último o un indicador
                        if ($saldoRestante <= 0.01) {
                            $numPagoCalculado = $totalPagosEsperados; // Dejar en el último
                        }
                    }
                }
                
                $pendiente = 0;
                if ($saldoRestante <= 0.01) {
                    $pendiente = 0;
                } elseif ($pagoSugerido > 0) {
                    // Si estamos en el último pago (o más allá), sugerir liquidar todo el saldo restante
                    if ($numPagoCalculado >= $totalPagosEsperados && $totalPagosEsperados > 0) {
                        $pendiente = $saldoRestante;
                    } else {
                        $pagoSugeridoCentavos = (int) round($pagoSugerido * 100);
                        $totalPagadoCentavos = (int) round($totalPagado * 100);
                        
                        $restoCentavos = ($pagoSugeridoCentavos > 0) ? $totalPagadoCentavos % $pagoSugeridoCentavos : 0;
                        
                        if ($restoCentavos == 0) {
                            $pendiente = $pagoSugerido;
                        } else {
                            $pendiente = ($pagoSugeridoCentavos - $restoCentavos) / 100;
                        }

                        // Cap pendiente at saldo restante
                        if ($pendiente > $saldoRestante) {
                            $pendiente = $saldoRestante;
                        }
                    }
                }

                // Calcular Moratorio (Usando lógica robusta del modelo)
                try {
                    $moratorio = $this->prestamo->calcularMoratorioVigente($cliente->id, $montoAutorizado);
                } catch (\Exception $e) {
                    $moratorio = $this->calcularMoratorio($cliente->id, $montoAutorizado, $pagoSugerido); // Fallback old logic
                }

                $this->pendientes[$cliente->id] = $pendiente;
                $this->moratorios[$cliente->id] = $moratorio;
                $this->siguientesPagos[$cliente->id] = $numPagoCalculado;
                
                $this->abonos[$cliente->id] = $pendiente; // Sugerir pagar el pendiente exacto
                $this->selectedClients[$cliente->id] = true;
            }
            $this->selectAll = true;
        }
    }

    public function calcularTotalAdeudo($montoAutorizado)
    {
        if ($montoAutorizado <= 0) {
            return 0;
        }

        $plazo = strtolower(trim($this->prestamo->plazo));
        $periodicidad = strtolower(trim($this->prestamo->periodicidad));
        $tasaInteres = (float) $this->prestamo->tasa_interes;

        // Determinar configuración según reglas de negocio
        $configuracion = $this->determinarConfiguracionPago($plazo, $periodicidad);

        if (! $configuracion) {
            // Fallback: cálculo básico
            $interesTotal = $montoAutorizado * ($tasaInteres / 100);
            return $montoAutorizado + $interesTotal;
        }

        // Calcular según reglas de negocio específicas
        $interes = (($montoAutorizado / 100) * $tasaInteres) * $configuracion['meses_interes'];
        $ivaPorcentaje = \App\Models\Configuration::get('iva_percentage', 16);
        $iva = ($interes / 100) * $ivaPorcentaje;
        
        return $interes + $iva + $montoAutorizado;
    }

    public function calcularCuota($montoAutorizado)
    {
        if ($montoAutorizado <= 0) {
            return 0;
        }

        $plazo = strtolower(trim($this->prestamo->plazo));
        $periodicidad = strtolower(trim($this->prestamo->periodicidad));
        $tasaInteres = (float) $this->prestamo->tasa_interes;

        // Determinar configuración según reglas de negocio
        $configuracion = $this->determinarConfiguracionPago($plazo, $periodicidad);

        if (! $configuracion) {
            // Fallback: cálculo básico
            $interesTotal = $montoAutorizado * ($tasaInteres / 100);
            $montoTotal = $montoAutorizado + $interesTotal;
            $plazoNum = preg_match('/(\d+)/', $plazo, $matches) ? (int) $matches[1] : 1;

            return round($montoTotal / $plazoNum, 2);
        }

        // Calcular según reglas de negocio específicas
        $interes = (($montoAutorizado / 100) * $tasaInteres) * $configuracion['meses_interes'];
        $ivaPorcentaje = \App\Models\Configuration::get('iva_percentage', 16);
        $iva = ($interes / 100) * $ivaPorcentaje;
        $montoTotal = $interes + $iva + $montoAutorizado;

        // Calcular pago regular
        $pagoConDecimales = $montoTotal / $configuracion['total_pagos'];

        return floor($pagoConDecimales);
    }

    protected function determinarConfiguracionPago(string $plazo, string $periodicidad): ?array
    {
        $configuraciones = [
            // Caso 1: 4 meses
            '4 meses_semanal' => ['meses_interes' => 4, 'total_pagos' => 16],
            '4meses_semanal' => ['meses_interes' => 4, 'total_pagos' => 16],
            '4 meses_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 8],
            '4meses_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 8],
            '4 meses_quincenal' => ['meses_interes' => 4, 'total_pagos' => 8],
            '4meses_quincenal' => ['meses_interes' => 4, 'total_pagos' => 8],

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

        $clave = $plazo.'_'.$periodicidad;

        return $configuraciones[$clave] ?? null;
    }

    public function calcularMoratorio($clienteId, $montoAutorizado, $cuota)
    {
        if ($cuota <= 0) return 0;

        $calendario = $this->generarCalendarioPagos($montoAutorizado);
        $pagosVencidos = 0;
        $fechaHoy = now()->startOfDay();
        
        // Obtener pagos realizados por el cliente
        $pagosRealizados = $this->prestamo->pagos->where('cliente_id', $clienteId);
        
        foreach ($calendario as $pagoProg) {
            $fechaVenc = Carbon::parse($pagoProg['fecha'])->startOfDay();
            
            // Si la fecha de vencimiento ya pasó
            if ($fechaVenc->lt($fechaHoy)) {
                $montoEsperado = $pagoProg['monto'];
                
                // Buscar pagos realizados para este número de pago
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

    public function generarCalendarioPagos($montoAutorizado)
    {
        $plazo = strtolower(trim($this->prestamo->plazo));
        $periodicidad = strtolower(trim($this->prestamo->periodicidad));
        $fechaPrimerPago = $this->prestamo->fecha_primer_pago ? Carbon::parse($this->prestamo->fecha_primer_pago) : now();

        $config = $this->determinarConfiguracionPago($plazo, $periodicidad);
        
        if (!$config) {
            // Fallback básico
            $plazoNum = preg_match('/(\d+)/', $plazo, $matches) ? (int) $matches[1] : 1;
            $numeroPagos = $plazoNum; 
            if (str_contains($periodicidad, 'seman')) $numeroPagos = $plazoNum * 4;
            elseif (str_contains($periodicidad, 'quincen')) $numeroPagos = $plazoNum * 2;
            elseif (str_contains($periodicidad, 'mens')) $numeroPagos = $plazoNum;
        } else {
             $numeroPagos = $config['total_pagos'];
        }

        // Calcular cuota
        $cuota = $this->calcularCuota($montoAutorizado);

        $calendario = [];
        $fechaActual = $fechaPrimerPago->copy();
        
        $intervaloDias = match(true) {
            str_contains($periodicidad, 'seman') => 7,
            str_contains($periodicidad, 'catorcen') => 14,
            str_contains($periodicidad, 'quincen') => 14, // Consistencia con PDF
            str_contains($periodicidad, 'mens') => 30,
            default => 7
        };

        // Cargar feriados
        $diasFeriados = Holiday::whereYear('date', $fechaActual->year)
            ->orWhereYear('date', $fechaActual->copy()->addYear()->year)
            ->pluck('date')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        for ($i = 1; $i <= $numeroPagos; $i++) {
            if ($i === 1) {
                $fechaPago = $fechaActual->copy();
            } else {
                $fechaPago = $fechaActual->copy()->addDays($intervaloDias);
                
                // Ajuste feriados y domingos
                while (in_array($fechaPago->format('Y-m-d'), $diasFeriados) || $fechaPago->dayOfWeek === Carbon::SUNDAY) {
                    $fechaPago->addDay();
                }
            }

            $calendario[] = [
                'numero' => $i,
                'fecha' => $fechaPago->format('Y-m-d'),
                'monto' => $cuota,
            ];

            $fechaActual = $fechaPago->copy();
        }

        return $calendario;
    }

    public function irACobrar()
    {
        if ($this->prestamo && in_array($this->prestamo->estado, ['entregado', 'liquidado'])) {
            // Guardar estado en caché por 5 minutos para recuperarlo en la siguiente vista
            $cacheKey = 'cobro_data_' . auth()->id() . '_' . $this->prestamo->id;
            \Illuminate\Support\Facades\Cache::put($cacheKey, [
                'abonos' => $this->abonos,
                'selectedClients' => $this->selectedClients,
            ], now()->addMinutes(5));

            return redirect()->route('pagos.desglose-efectivo', $this->prestamo->id);
        }
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.pagos.index');
    }
}
