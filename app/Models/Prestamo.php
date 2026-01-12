<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $table = 'prestamos';

    protected $fillable = [
        'folio', 'producto', 'monto_total', 'monto_sugerido', 'plazo', 'periodicidad', 'periodo_pago', 'dia_pago', 'fecha_entrega', 'fecha_primer_pago', 'tasa_interes', 'garantia', 'estado', 'autorizado_por', 'cliente_id', 'grupo_id', 'representante_id', 'asesor_id', 'motivo_rechazo', 'comentarios_comite', 'desglose_entrega',
    ];

    protected $casts = [
        'fecha_entrega' => 'date',
        'fecha_primer_pago' => 'date',
        'monto_total' => 'decimal:2',
        'monto_sugerido' => 'decimal:2',
        'tasa_interes' => 'decimal:4',
        'garantia' => 'decimal:2',
        'desglose_entrega' => 'array',
    ];

    // Generador de folio deshabilitado - ahora usamos el ID directamente
    // protected static function booted()
    // {
    //     static::creating(function ($model) {
    //         if (empty($model->folio)) {
    //             // generar folio único: PRE-YYYYMMDD-NNNN
    //             $date = date('Ymd');
    //             $prefix = "PRE-{$date}-";
    //             $last = self::where('folio', 'like', "{$prefix}%")->orderByDesc('id')->first();
    //             $num = 1;
    //             if ($last && preg_match('/-(\d+)$/', $last->folio, $m)) {
    //                 $num = intval($m[1]) + 1;
    //             }
    //             $model->folio = $prefix.str_pad($num, 4, '0', STR_PAD_LEFT);
    //         }
    //     });
    // }

    public function prestamoable()
    {
        return $this->morphTo();
    }

    // relación 1 a 1 con cliente si producto == 'individual'
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Representante del préstamo: cliente designado para préstamos grupales o el mismo cliente en individuales
     */
    public function representante()
    {
        return $this->belongsTo(Cliente::class, 'representante_id');
    }

    /**
     * Grupo al que pertenece el préstamo (solo para préstamos grupales)
     */
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    // relación muchos a muchos para prestamos grupales
    public function clientes()
    {
        return $this->belongsToMany(Cliente::class, 'cliente_prestamo', 'prestamo_id', 'cliente_id')
            ->withPivot('monto_solicitado', 'monto_sugerido', 'monto_autorizado')
            ->withTimestamps();
    }

    public function autorizador()
    {
        return $this->belongsTo(User::class, 'autorizado_por');
    }

    /**
     * Asesor asignado al préstamo
     */
    public function asesor()
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    /**
     * Calcular el total solicitado sumando desde la tabla pivot
     */
    public function calcularTotalSolicitado(): float
    {
        if ($this->producto === 'grupal' && $this->clientes && $this->clientes->count() > 0) {
            return $this->clientes->sum(function ($cliente) {
                return (float) ($cliente->pivot->monto_solicitado ?? 0);
            });
        }

        // Para individuales, usar el monto_total o buscar en pivot
        if ($this->cliente_id && $this->clientes && $this->clientes->count() > 0) {
            $cliente = $this->clientes->first();

            return (float) ($cliente->pivot->monto_solicitado ?? $this->monto_total ?? 0);
        }

        return (float) ($this->monto_total ?? 0);
    }

    /**
     * Calcular el total autorizado sumando desde la tabla pivot
     */
    public function calcularTotalAutorizado(): float
    {
        $total = 0;

        if ($this->clientes && $this->clientes->count() > 0) {
            $total = $this->clientes->sum(function ($cliente) {
                return (float) ($cliente->pivot->monto_autorizado ?? 0);
            });
        }

        // Si está autorizado pero el total es 0, asumir que es el total solicitado (migración/fallback)
        if ($this->estado === 'autorizado' && $total <= 0) {
            return $this->calcularTotalSolicitado();
        }

        return $total;
    }

    public function autorizar(User $user): void
    {
        $this->estado = 'autorizado';
        $this->autorizado_por = $user->id;

        // Primero, asegurar que todos los clientes tengan monto_autorizado
        // Si es null, usar el monto_solicitado como autorizado
        $this->load('clientes'); // Recargar para asegurar datos frescos de la BD
        foreach ($this->clientes as $cliente) {
            if ($cliente->pivot->monto_autorizado === null) {
                $montoSolicitado = $cliente->pivot->monto_solicitado ?? 0;
                $this->clientes()->updateExistingPivot($cliente->id, [
                    'monto_autorizado' => $montoSolicitado,
                ]);
            }
        }

        // Recargar la relación para obtener los datos actualizados
        $this->load('clientes');

        // Calcular el monto total autorizado después de la actualización
        $totalAutorizado = $this->calcularTotalAutorizado();

        // Actualizar monto_total del préstamo
        if ($totalAutorizado > 0) {
            $this->monto_total = $totalAutorizado;
        }

        $this->save();

        // Aquí se podría disparar la lógica de descuento de capital o eventos.
    }

    public function rechazar(User $user, ?string $motivo = null): void
    {
        $this->estado = 'rechazado';
        $this->autorizado_por = $user->id;
        $this->motivo_rechazo = $motivo;
        $this->save();
    }

    public function bitacora()
    {
        return $this->hasMany(PrestamoBitacora::class)->orderByDesc('created_at');
    }

    public function registrarBitacora(string $accion, ?string $comentarios = null): void
    {
        $this->bitacora()->create([
            'user_id' => auth()->id(),
            'accion' => $accion,
            'comentarios' => $comentarios,
        ]);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class)->orderBy('fecha_pago');
    }

    /**
     * Calcular el total pagado sumando todos los pagos
     */
    public function calcularTotalPagado(): float
    {
        return $this->pagos()->sum('monto');
    }

    /**
     * Calcular el monto total de la deuda (Capital + Interés + IVA)
     */
    public function calcularMontoTotalDeuda(): float
    {
        $monto = (float) $this->monto_total;
        $tasaInteres = (float) $this->tasa_interes;
        $plazo = strtolower(trim($this->plazo));
        $periodicidad = strtolower(trim($this->periodicidad));
        
        $config = $this->determinarConfiguracionPago($plazo, $periodicidad);
        
        if (!$config) {
            // Fallback: cálculo básico
             $interesTotal = $monto * ($tasaInteres / 100);
             return $monto + $interesTotal;
        }

        $interes = (($monto / 100) * $tasaInteres) * $config['meses_interes'];
        $ivaPorcentaje = \App\Models\Configuration::get('iva_percentage', 16);
        $iva = ($interes / 100) * $ivaPorcentaje;
        
        return $monto + $interes + $iva;
    }

    /**
     * Calcular el saldo pendiente real considerando intereses e IVA
     */
    public function calcularSaldoPendiente(): float
    {
        return $this->calcularMontoTotalDeuda() - $this->calcularTotalPagado();
    }

    /**
     * Aplica pagos sin `numero_pago` a cuotas en orden (FIFO).
     *
     * @param  array<int, array{numero: mixed, monto: mixed}>  $calendarioPagos
     * @param  array<int|string, float|int|string>  $pagadoPorNumero
     * @return array{pagadoPorNumero: array<int, float>, remanente: float}
     */
    public static function aplicarPagosSinNumeroAFifo(array $calendarioPagos, array $pagadoPorNumero, float $pagosSinNumeroTotal): array
    {
        $pagado = [];
        foreach ($pagadoPorNumero as $numero => $monto) {
            $pagado[(int) $numero] = (float) $monto;
        }

        $remanente = (float) $pagosSinNumeroTotal;

        foreach ($calendarioPagos as $pagoProg) {
            $numero = (int) ($pagoProg['numero'] ?? 0);
            $esperado = (float) ($pagoProg['monto'] ?? 0);

            if ($numero <= 0 || $esperado <= 0 || $remanente <= 0) {
                continue;
            }

            $pagadoActual = (float) ($pagado[$numero] ?? 0);
            $falta = $esperado - $pagadoActual;

            if ($falta <= 0) {
                continue;
            }

            $aplicar = min($falta, $remanente);
            $pagado[$numero] = $pagadoActual + $aplicar;
            $remanente -= $aplicar;
        }

        return [
            'pagadoPorNumero' => $pagado,
            'remanente' => $remanente,
        ];
    }

    /**
     * Calcula el monto vencido por cuota: usa pagos por `numero_pago` y reparte pagos sin número en FIFO.
     *
     * @param  array<int, array{numero: mixed, fecha: string, monto: mixed}>  $calendarioPagos
     * @param  array<int|string, float|int|string>  $pagadoPorNumero
     */
    public static function calcularMontoVencidoDesdeCalendario(array $calendarioPagos, Carbon $fechaHoy, array $pagadoPorNumero, float $pagosSinNumeroTotal = 0): float
    {
        $fechaHoy = $fechaHoy->copy()->startOfDay();

        $resultado = self::aplicarPagosSinNumeroAFifo($calendarioPagos, $pagadoPorNumero, $pagosSinNumeroTotal);
        $pagado = $resultado['pagadoPorNumero'];

        $montoVencido = 0.0;

        foreach ($calendarioPagos as $pagoProg) {
            $fecha = (string) ($pagoProg['fecha'] ?? '');
            $monto = (float) ($pagoProg['monto'] ?? 0);
            $numero = (int) ($pagoProg['numero'] ?? 0);

            try {
                $fechaVenc = Carbon::createFromFormat('d-m-y', $fecha)->startOfDay();
            } catch (\Throwable $e) {
                $fechaVenc = Carbon::parse($fecha)->startOfDay();
            }

            if ($fechaVenc->lte($fechaHoy)) {
                $pagadoCuota = (float) ($pagado[$numero] ?? 0);
                if ($pagadoCuota < $monto) {
                    $montoVencido += ($monto - $pagadoCuota);
                }
            }
        }

        return max(0.0, $montoVencido);
    }

    /**
     * Cuenta atrasos por cuota: usa pagos por `numero_pago` y reparte pagos sin número en FIFO.
     *
     * @param  array<int, array{numero: mixed, fecha: string, monto: mixed}>  $calendarioPagos
     * @param  array<int|string, float|int|string>  $pagadoPorNumero
     */
    public static function calcularAtrasosDesdeCalendario(array $calendarioPagos, Carbon $fechaHoy, array $pagadoPorNumero, float $pagosSinNumeroTotal = 0, float $tolerancia = 1): int
    {
        $fechaHoy = $fechaHoy->copy()->startOfDay();

        $resultado = self::aplicarPagosSinNumeroAFifo($calendarioPagos, $pagadoPorNumero, $pagosSinNumeroTotal);
        $pagado = $resultado['pagadoPorNumero'];

        $atrasos = 0;

        foreach ($calendarioPagos as $pagoProg) {
            $fecha = (string) ($pagoProg['fecha'] ?? '');
            $monto = (float) ($pagoProg['monto'] ?? 0);
            $numero = (int) ($pagoProg['numero'] ?? 0);

            try {
                $fechaVenc = Carbon::createFromFormat('d-m-y', $fecha)->startOfDay();
            } catch (\Throwable $e) {
                $fechaVenc = Carbon::parse($fecha)->startOfDay();
            }

            if ($fechaVenc->lt($fechaHoy)) {
                $pagadoCuota = (float) ($pagado[$numero] ?? 0);
                if ($pagadoCuota < ($monto - $tolerancia)) {
                    $atrasos++;
                }
            }
        }

        return $atrasos;
    }

    public function extraerPlazoNumerico($plazo)
    {
        if (is_numeric($plazo)) {
            return (int) $plazo;
        }
        preg_match('/(\d+)/', $plazo, $matches);
        return isset($matches[1]) ? (int) $matches[1] : 1;
    }

    public function determinarConfiguracionPago($plazo, $periodicidad)
    {
        $configuraciones = [
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
            '4 meses d_semanal' => ['meses_interes' => 4, 'total_pagos' => 14],
            '4meses d_semanal' => ['meses_interes' => 4, 'total_pagos' => 14],
            '4mesesd_semanal' => ['meses_interes' => 4, 'total_pagos' => 14],
            '4 meses d_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4meses d_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4mesesd_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4 meses d_quincenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4meses d_quincenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4mesesd_quincenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '5 meses d_semanal' => ['meses_interes' => 5, 'total_pagos' => 18],
            '5meses d_semanal' => ['meses_interes' => 5, 'total_pagos' => 18],
            '5mesesd_semanal' => ['meses_interes' => 5, 'total_pagos' => 18],
            '5 meses d_catorcenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5meses d_catorcenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5mesesd_catorcenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5 meses d_quincenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5meses d_quincenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5mesesd_quincenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '6 meses_semanal' => ['meses_interes' => 6, 'total_pagos' => 24],
            '6meses_semanal' => ['meses_interes' => 6, 'total_pagos' => 24],
            '6 meses_catorcenal' => ['meses_interes' => 6, 'total_pagos' => 12],
            '6meses_catorcenal' => ['meses_interes' => 6, 'total_pagos' => 12],
            '6 meses_quincenal' => ['meses_interes' => 6, 'total_pagos' => 12],
            '6meses_quincenal' => ['meses_interes' => 6, 'total_pagos' => 12],
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

    public function simularCalendarioPago(float $monto, ?string $fechaInicio = null): array
    {
        $monto = (float) $monto;
        $tasaInteres = (float) $this->tasa_interes;
        $plazo = strtolower(trim($this->plazo));
        $periodicidad = strtolower(trim($this->periodicidad));
        $fechaPrimerPago = $fechaInicio ?? $this->fecha_primer_pago ?? now();
        
        $config = $this->determinarConfiguracionPago($plazo, $periodicidad);
        
        if (!$config) {
            // Implementación básica simplificada si no hay configuración
            // Se puede expandir luego copiando calcularCalendarioBasico si es necesario
             $interesTotal = $monto * ($tasaInteres / 100);
             $montoTotal = $monto + $interesTotal;
             return []; // Fallback simple o implementar completo
        }

        $interes = (($monto / 100) * $tasaInteres) * $config['meses_interes'];
        $ivaPorcentaje = \App\Models\Configuration::get('iva_percentage', 16);
        $iva = ($interes / 100) * $ivaPorcentaje;
        $montoTotal = $interes + $iva + $monto;
        
        $numeroPagos = $config['total_pagos'];

        $calendario = [];
        $fechaActual = Carbon::parse($fechaPrimerPago);

        $diasFeriados = \App\Models\Holiday::whereYear('date', $fechaActual->year)
            ->orWhereYear('date', $fechaActual->copy()->addYear()->year)
            ->pluck('date')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        // Determinar intervalo en días según periodicidad
        $intervaloDias = match($periodicidad) {
            'semanal', 'semana', 'weekly' => 7,
            'catorcenal', 'quincenal', 'quincena', 'biweekly' => 14,
            'mensual', 'mes', 'monthly' => 30,
            default => 7
        };

        // Calcular monto por pago usando lógica de decimales
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

            // Nota: Aquí no verificamos ultimoPago variable como en PDF porque asumimos dinámico
            
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

    public function calcularMoratorioVigente($clienteId, $montoAutorizado)
    {
        $calendario = $this->simularCalendarioPago($montoAutorizado);
        $fechaHoy = now();
        
        $pagosCliente = $this->pagos->where('cliente_id', $clienteId);
        $pagadoPorNumero = $pagosCliente->whereNotNull('numero_pago')
            ->groupBy('numero_pago')
            ->map(fn ($pagos) => (float) $pagos->sum('monto'))
            ->toArray();
        $pagosSinNumeroTotal = (float) $pagosCliente->whereNull('numero_pago')->sum('monto');

        $atrasos = self::calcularAtrasosDesdeCalendario(
            $calendario,
            $fechaHoy,
            $pagadoPorNumero,
            $pagosSinNumeroTotal,
            1
        );

        if ($atrasos <= 0) return 0;

        $tasaInteres = (float) $this->tasa_interes;
        $config = $this->determinarConfiguracionPago(
            strtolower(trim($this->plazo)), 
            strtolower(trim($this->periodicidad))
        );
        
        $mesesInteres = $config ? $config['meses_interes'] : 4;
        
        $interesTotalMulta = ($montoAutorizado / 100) * $tasaInteres * $mesesInteres;
        $configPagos = $config ? $config['total_pagos'] : count($calendario);
        
        $capitalPorPagoMulta = $configPagos > 0 ? $montoAutorizado / $configPagos : 0;
        $baseMulta = $interesTotalMulta + $capitalPorPagoMulta;
        $multaUnitaria = $baseMulta * 0.05;
        
        return $atrasos * $multaUnitaria;
    }

    public function calcularSaldoLiquidarParaCliente($clienteId, $montoAutorizado)
    {
        // 1. Calcular Deuda Original Total (Capital + Interes + IVA)
        $tasaInteres = (float) $this->tasa_interes;
        $config = $this->determinarConfiguracionPago(
            strtolower(trim($this->plazo)), 
            strtolower(trim($this->periodicidad))
        );
        
        $mesesInteres = $config ? $config['meses_interes'] : 4;
        
        $interesBase = (($montoAutorizado / 100) * $tasaInteres) * $mesesInteres;
        $ivaPorcentaje = \App\Models\Configuration::get('iva_percentage', 16);
        $ivaBase = ($interesBase / 100) * $ivaPorcentaje;
        
        $totalDeudaOriginal = $montoAutorizado + $interesBase + $ivaBase;
        
        // 2. Obtener Pagos Realizados (incluye moratorios pagados, capital, e interes)
        $pagosCliente = $this->pagos->where('cliente_id', $clienteId);
        $totalPagadoReal = $pagosCliente->sum('monto');
        $moratoriosPagados = $pagosCliente->sum('moratorio_pagado');
        
        // 3. Calcular Moratorios Vigentes (generados hoy)
        $saldoMoratorio = $this->calcularMoratorioVigente($clienteId, $montoAutorizado);
        
        // Formula Final:
        // Saldo = (DeudaOriginal - (PagadoTotal - MoratoriosPagados)) + MoratoriosVigentes
        // Explicación: Los pagos cubren moratorios primero. De lo que sobra, cubre deuda original.
        // Si pagué $100 de moratorio, no bajó mi deuda original.
        // Si pagué $1000 ($100 mora + $900 capital), mi deuda bajó $900.
        // PagadoTotal - MoratoriosPagados = $900.
        // DeudaOriginal - $900 = SaldoCapital.
        // SaldoLiquidar = SaldoCapital + NuevosMoratorios.
        
        $saldoLiquidar = $totalDeudaOriginal - ($totalPagadoReal - $moratoriosPagados) + $saldoMoratorio;
        
        return max(0, $saldoLiquidar);
    }
}
