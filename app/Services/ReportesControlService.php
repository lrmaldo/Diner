<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Prestamo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportesControlService
{
    public function __construct() {}

    public function calcularCarteraPorAsesor(Carbon $fechaCorte)
    {
        // Esta es la estructura que devolveremos, iterando primero a los Asesores
        $asesores = User::whereHas('prestamosComoAsesor', function ($q) use ($fechaCorte) {
            $q->whereIn('estado', ['Entregado', 'Atrasado'])
                ->where('fecha_entrega', '<=', $fechaCorte);
        })->with(['prestamosComoAsesor' => function ($q) use ($fechaCorte) {
            $q->whereIn('estado', ['Entregado', 'Atrasado'])
                ->where('fecha_entrega', '<=', $fechaCorte)
                ->with(['pagos', 'cliente']);
        }])->get();

        $resultados = [];
        $clientesDetalle = [];
        $clientesPorBucket = [
            'c_vigente' => [],
            'cv_1_7' => [],
            'cv_8_30' => [],
            'cv_31_90' => [],
            'cv_91_180' => [],
            'cv_181_365' => [],
            'cv_mas_365' => [],
        ];
        $totalesGlobales = [
            'c_vigente' => ['saldo' => 0, 'clientes' => 0, 'creditos' => 0, 'porcentaje' => 0],
            'cv_1_7' => ['saldo' => 0, 'clientes' => 0, 'creditos' => 0,  'porcentaje' => 0],
            'cv_8_30' => ['saldo' => 0, 'clientes' => 0, 'creditos' => 0, 'porcentaje' => 0],
            'cv_31_90' => ['saldo' => 0, 'clientes' => 0, 'creditos' => 0, 'porcentaje' => 0],
            'cv_91_180' => ['saldo' => 0, 'clientes' => 0, 'creditos' => 0, 'porcentaje' => 0],
            'cv_181_365' => ['saldo' => 0, 'clientes' => 0, 'creditos' => 0, 'porcentaje' => 0],
            'cv_mas_365' => ['saldo' => 0, 'clientes' => 0, 'creditos' => 0, 'porcentaje' => 0],
            'cv_total' => ['saldo' => 0, 'clientes' => 0, 'porcentaje' => 0],
            'creditos' => 0,
            'clientes' => 0,
            'saldo_total' => 0,
        ];

        foreach ($asesores as $asesor) {
            $dataAsesor = [
                'asesor' => $asesor->name,
                'c_vigente' => ['saldo' => 0, 'clientes' => 0, 'creditos' => 0],
                'cv_1_7' => ['saldo' => 0, 'clientes' => 0, 'creditos' => 0],
                'cv_8_30' => ['saldo' => 0, 'clientes' => 0, 'creditos' => 0],
                'cv_31_90' => ['saldo' => 0, 'clientes' => 0, 'creditos' => 0],
                'cv_91_180' => ['saldo' => 0, 'clientes' => 0, 'creditos' => 0],
                'cv_181_365' => ['saldo' => 0, 'clientes' => 0, 'creditos' => 0],
                'cv_mas_365' => ['saldo' => 0, 'clientes' => 0, 'creditos' => 0],
                'cv_total' => ['saldo' => 0, 'clientes' => 0],
                'creditos' => 0,
                'clientes' => 0,
                'saldo_total' => 0,
            ];

            $clasificacionClientesAsesor = [];

            foreach ($asesor->prestamosComoAsesor as $prestamo) {
                // Filtrar pagos hasta la fecha de corte
                $pagosHastaFecha = $prestamo->pagos->where('fecha_pago', '<=', $fechaCorte);
                // Usar Capital Pagado que ya tiene Diner en tabla Pagos
                $capitalPagado = $pagosHastaFecha->sum('capital_pagado');
                $capitalAEntregar = $prestamo->monto_autorizado ?? $prestamo->monto_total;

                $saldoRestante = max(0, $capitalAEntregar - $capitalPagado);

                if ($saldoRestante <= 0.01) {
                    continue;
                } // Préstamo Liquidado a esa fecha

                // Verificar si han pasado más de 365 días desde el último pago
                $ultimoPago = $pagosHastaFecha->sortByDesc('fecha_pago')->first();
                $diasDesdeUltimoPago = 0;

                if ($ultimoPago) {
                    $fechaUltimoPago = Carbon::parse($ultimoPago->fecha_pago);
                    $diasDesdeUltimoPago = $fechaUltimoPago->diffInDays($fechaCorte);
                } else {
                    // Si no hay pagos, calcular desde la fecha de entrega
                    $fechaEntrega = Carbon::parse($prestamo->fecha_entrega);
                    $diasDesdeUltimoPago = $fechaEntrega->diffInDays($fechaCorte);
                }

                // Si han pasado más de 365 días desde el último pago, no contar como cliente activo
                if ($diasDesdeUltimoPago > 365) {
                    continue;
                }

                // Calcular dias de Atraso
                $pagadoPorNumero = [];
                $pagosSinNumeroTotal = 0;
                foreach ($pagosHastaFecha as $p) {
                    if ($p->numero_pago) {
                        $pagadoPorNumero[$p->numero_pago] = ($pagadoPorNumero[$p->numero_pago] ?? 0) + $p->monto;
                    } else {
                        $pagosSinNumeroTotal += $p->monto;
                    }
                }

                $calendario = CalculadoraPrestamos::calcularCalendarioPagos(
                    $capitalAEntregar,
                    $prestamo->tasa_interes,
                    $prestamo->plazo,
                    $prestamo->periodicidad,
                    $prestamo->fecha_primer_pago ?? $prestamo->fecha_entrega,
                    $prestamo->ultimo_pago ?? null
                );

                $diasAtraso = 0;
                // Una forma conservadora de sacar dias de atraso es ver cual es la cuota mas antigua vencida y no pagada
                $pagadoAcum = 0;
                // Simplificando simulacion usando logica del prestamo de la APP
                $atrasosCuotas = Prestamo::calcularAtrasosDesdeCalendario($calendario, $fechaCorte, $pagadoPorNumero, $pagosSinNumeroTotal);

                if ($atrasosCuotas > 0) {
                    // Hay atraso. Convertir cuotas a dias
                    // Aproximacion: si periocidad es semanal, atrasos * 7 dias
                    $diasPorCuota = 7;
                    if (str_contains(strtolower($prestamo->periodicidad), 'quincenal')) {
                        $diasPorCuota = 15;
                    }
                    if (str_contains(strtolower($prestamo->periodicidad), 'mensual')) {
                        $diasPorCuota = 30;
                    }
                    if (str_contains(strtolower($prestamo->periodicidad), 'catorcenal')) {
                        $diasPorCuota = 14;
                    }

                    $diasAtraso = $atrasosCuotas * $diasPorCuota;
                }

                $bucket = 'c_vigente';
                if ($diasAtraso >= 1 && $diasAtraso <= 7) {
                    $bucket = 'cv_1_7';
                } elseif ($diasAtraso >= 8 && $diasAtraso <= 30) {
                    $bucket = 'cv_8_30';
                } elseif ($diasAtraso >= 31 && $diasAtraso <= 90) {
                    $bucket = 'cv_31_90';
                } elseif ($diasAtraso >= 91 && $diasAtraso <= 180) {
                    $bucket = 'cv_91_180';
                } elseif ($diasAtraso >= 181 && $diasAtraso <= 365) {
                    $bucket = 'cv_181_365';
                } elseif ($diasAtraso > 365) {
                    $bucket = 'cv_mas_365';
                }

                $dataAsesor[$bucket]['saldo'] += $saldoRestante;
                $dataAsesor[$bucket]['creditos'] += 1;
                $dataAsesor['creditos'] += 1;

                // Todos los préstamos que llegaron aquí son clientes activos
                // (ya se filtró por saldo > 0 y último pago dentro de 365 días)
                $dataAsesor['saldo_total'] += $saldoRestante;

                if ($prestamo->cliente_id) {
                    $clienteNombre = trim(implode(' ', array_filter([
                        $prestamo->cliente->nombres ?? null,
                        $prestamo->cliente->apellido_paterno ?? null,
                        $prestamo->cliente->apellido_materno ?? null,
                    ])));

                    $clienteActual = $clientesDetalle[$prestamo->cliente_id] ?? null;
                    $fechaEntregaActual = $clienteActual['fecha_entrega'] ?? null;
                    $fechaEntregaNueva = $prestamo->fecha_entrega ? Carbon::parse($prestamo->fecha_entrega)->toDateString() : null;

                    if (! $clienteActual || ($fechaEntregaNueva && $fechaEntregaActual && $fechaEntregaNueva > $fechaEntregaActual) || ($fechaEntregaNueva && ! $fechaEntregaActual)) {
                        $clientesDetalle[$prestamo->cliente_id] = [
                            'cliente_id' => $prestamo->cliente_id,
                            'nombre' => $clienteNombre !== '' ? $clienteNombre : 'Cliente #'.$prestamo->cliente_id,
                            'curp' => $prestamo->cliente->curp ?? null,
                            'prestamo_id' => $prestamo->id,
                            'producto' => $prestamo->producto,
                            'fecha_entrega' => $fechaEntregaNueva,
                            'asesor' => $asesor->name,
                        ];
                    }

                    $clasificacionActual = $clasificacionClientesAsesor[$prestamo->cliente_id] ?? null;
                    $debeActualizarClasificacion = ! $clasificacionActual
                        || $diasAtraso > ($clasificacionActual['dias_atraso'] ?? -1);

                    if ($debeActualizarClasificacion) {
                        $clasificacionClientesAsesor[$prestamo->cliente_id] = [
                            'cliente_id' => $prestamo->cliente_id,
                            'dias_atraso' => $diasAtraso,
                            'bucket' => $bucket,
                            'nombre' => $clienteNombre !== '' ? $clienteNombre : 'Cliente #'.$prestamo->cliente_id,
                            'curp' => $prestamo->cliente->curp ?? null,
                            'prestamo_id' => $prestamo->id,
                            'producto' => $prestamo->producto,
                            'fecha_entrega' => $fechaEntregaNueva,
                            'asesor' => $asesor->name,
                        ];
                    }
                }
            }

            // Clasificar cliente en un único bucket por su mayor atraso dentro del asesor.
            $dataAsesor['clientes'] = count($clasificacionClientesAsesor);
            foreach (['c_vigente', 'cv_1_7', 'cv_8_30', 'cv_31_90', 'cv_91_180', 'cv_181_365', 'cv_mas_365'] as $col) {
                $dataAsesor[$col]['clientes'] = 0;
            }

            foreach ($clasificacionClientesAsesor as $clienteClasificado) {
                $bucketCliente = $clienteClasificado['bucket'];
                if (isset($dataAsesor[$bucketCliente])) {
                    $dataAsesor[$bucketCliente]['clientes'] += 1;
                }

                $clienteId = $clienteClasificado['cliente_id'];
                $actualGlobalBucket = $clientesPorBucket[$bucketCliente][$clienteId] ?? null;
                if (! $actualGlobalBucket || ($clienteClasificado['dias_atraso'] ?? 0) > ($actualGlobalBucket['dias_atraso'] ?? -1)) {
                    $clientesPorBucket[$bucketCliente][$clienteId] = $clienteClasificado;
                }
            }

            // Calculando CV Total (suma  1 a 365 dias)
            $cvTotal = 0;
            $cvTotalClientes = 0;
            $buckestVencidos = ['cv_1_7', 'cv_8_30', 'cv_31_90', 'cv_91_180', 'cv_181_365'];
            foreach ($buckestVencidos as $bk) {
                $cvTotal += $dataAsesor[$bk]['saldo'];
                $cvTotalClientes += $dataAsesor[$bk]['clientes'];
            }
            $dataAsesor['cv_total'] = ['saldo' => $cvTotal, 'clientes' => $cvTotalClientes];

            // Porcentajes
            foreach (['c_vigente', 'cv_1_7', 'cv_8_30', 'cv_31_90', 'cv_91_180', 'cv_181_365', 'cv_mas_365'] as $col) {
                $dataAsesor[$col]['porcentaje'] = $dataAsesor['saldo_total'] > 0 ? round(($dataAsesor[$col]['saldo'] / $dataAsesor['saldo_total']) * 100, 2) : 0;

                // Sumar globales
                $totalesGlobales[$col]['saldo'] += $dataAsesor[$col]['saldo'];
                $totalesGlobales[$col]['clientes'] += $dataAsesor[$col]['clientes'];
                $totalesGlobales[$col]['creditos'] += $dataAsesor[$col]['creditos'];
            }
            $dataAsesor['cv_total']['porcentaje'] = $dataAsesor['saldo_total'] > 0 ? round(($cvTotal / $dataAsesor['saldo_total']) * 100, 2) : 0;

            $totalesGlobales['cv_total']['saldo'] += $dataAsesor['cv_total']['saldo'];
            $totalesGlobales['cv_total']['clientes'] += $dataAsesor['cv_total']['clientes'];

            $totalesGlobales['creditos'] += $dataAsesor['creditos'];
            $totalesGlobales['clientes'] += $dataAsesor['clientes'];
            $totalesGlobales['saldo_total'] += $dataAsesor['saldo_total'];

            if ($dataAsesor['creditos'] > 0) {
                $resultados[] = $dataAsesor;
            }
        }

        // Global Percentages
        foreach (['c_vigente', 'cv_1_7', 'cv_8_30', 'cv_31_90', 'cv_91_180', 'cv_181_365', 'cv_mas_365', 'cv_total'] as $col) {
            $totalesGlobales[$col]['porcentaje'] = $totalesGlobales['saldo_total'] > 0 ? round(($totalesGlobales[$col]['saldo'] / $totalesGlobales['saldo_total']) * 100, 2) : 0;
        }

        return [
            'asesores' => $resultados,
            'totales' => $totalesGlobales,
            'clientes_detalle' => array_values($clientesDetalle),
            'clientes_por_bucket' => array_map(static fn ($items) => array_values($items), $clientesPorBucket),
        ];
    }

    public function calcularEficienciaExigible(Carbon $inicio, Carbon $fin)
    {
        $prestamos = Prestamo::whereIn('estado', ['Entregado', 'Atrasado', 'Pagado', 'Liquidado'])
            ->where('fecha_entrega', '<=', $fin)
            ->with('pagos')
            ->get();

        $exigibleTotal = 0;
        $recuperadoTotal = 0;

        foreach ($prestamos as $prestamo) {
            try {
                $calendario = CalculadoraPrestamos::calcularCalendarioPagos(
                    $prestamo->monto_autorizado ?? $prestamo->monto_total,
                    $prestamo->tasa_interes,
                    $prestamo->plazo,
                    $prestamo->periodicidad,
                    $prestamo->fecha_primer_pago ?? $prestamo->fecha_entrega,
                    $prestamo->ultimo_pago ?? null
                );

                // FIFO
                $todosLosPagos = $prestamo->pagos->sortBy([['fecha_pago', 'asc'], ['id', 'asc']])->filter(function ($p) {
                    $tipo = strtolower($p->tipo_pago ?? '');

                    return ! in_array($tipo, ['garantia', 'garanti­a', 'seguro', 'cargo']) && ! str_contains($tipo, 'devolucion');
                });

                $colaPagos = [];
                foreach ($todosLosPagos as $p) {
                    $capitalNeto = (float) $p->monto - (float) $p->moratorio_pagado;
                    $colaPagos[] = [
                        'remanente' => max(0, $capitalNeto),
                    ];
                }

                $recuperadoPorCuota = [];
                foreach ($calendario as $c) {
                    $montoRequerido = (float) $c['monto'];
                    $pagadoParaEstaCuota = 0;

                    foreach ($colaPagos as &$entry) {
                        if ($entry['remanente'] <= 0.001) {
                            continue;
                        }

                        $tomar = min($entry['remanente'], $montoRequerido - $pagadoParaEstaCuota);
                        if ($tomar > 0) {
                            $pagadoParaEstaCuota += $tomar;
                            $entry['remanente'] -= $tomar;
                        }
                        if ($pagadoParaEstaCuota >= $montoRequerido - 0.001) {
                            break;
                        }
                    }
                    $recuperadoPorCuota[$c['numero']] = $pagadoParaEstaCuota;
                }

                foreach ($calendario as $cuota) {
                    $cFecha = Carbon::parse($cuota['fecha'])->startOfDay();
                    if ($cFecha->between($inicio->copy()->startOfDay(), $fin->copy()->endOfDay())) {
                        $exigibleTotal += $cuota['monto'];
                        $recuperadoTotal += ($recuperadoPorCuota[$cuota['numero']] ?? 0);
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        if ($exigibleTotal > 0) {
            return min(100, round(($recuperadoTotal / $exigibleTotal) * 100, 2));
        }

        return 0;
    }

    public function calcularMontoActivo(Carbon $fechaCorte)
    {
        $prestamos = Prestamo::whereIn('estado', ['Entregado', 'Atrasado'])
            ->where('fecha_entrega', '<=', $fechaCorte)
            ->with(['pagos' => function ($q) use ($fechaCorte) {
                $q->where('fecha_pago', '<=', $fechaCorte)->orderBy('fecha_pago', 'desc');
            }])
            ->get();

        $montoActivo = 0;

        foreach ($prestamos as $prestamo) {
            // "su fecha del ultimo pago"
            $fechaUltimoPagoEsperado = $prestamo->ultimo_pago ? Carbon::parse($prestamo->ultimo_pago) : null;

            // Si no tenemos fecha de vencimiento clara, la calculamos del calendario?
            if (! $fechaUltimoPagoEsperado) {
                try {
                    $calendario = CalculadoraPrestamos::calcularCalendarioPagos(
                        $prestamo->monto_autorizado ?? $prestamo->monto_total,
                        $prestamo->tasa_interes,
                        $prestamo->plazo,
                        $prestamo->periodicidad,
                        $prestamo->fecha_primer_pago ?? $prestamo->fecha_entrega,
                        null
                    );
                    if (count($calendario) > 0) {
                        $ultimaCuota = end($calendario);
                        $fechaUltimoPagoEsperado = Carbon::parse($ultimaCuota['fecha']);
                    }
                } catch (\Exception $e) {
                    // Ignorar
                }
            }

            if (! $fechaUltimoPagoEsperado) {
                $fechaUltimoPagoEsperado = Carbon::parse($prestamo->fecha_entrega)->addMonths(4); // Fallback conservador
            }

            // Validar condiciÃƒÂ³n:
            // 1. "que su fecha del ultimo pago no se aya pasado"
            if ($fechaUltimoPagoEsperado->copy()->endOfDay() >= $fechaCorte->copy()->startOfDay()) {
                $montoActivo += $prestamo->monto_total; // O monto_autorizado
            } else {
                // 2. o "si la fecha ya se paso que el ultimo deposito no exceda los 14 dÃƒÂ­as"
                $ultimoDeposito = $prestamo->pagos->first(); // Ya estÃƒÂ¡ ordenado desc y filtrado por fechaCorte
                if ($ultimoDeposito) {
                    $diasDesdeUltimoDeposito = Carbon::parse($ultimoDeposito->fecha_pago)->diffInDays($fechaCorte);
                    if ($diasDesdeUltimoDeposito <= 14) {
                        $montoActivo += $prestamo->monto_total;
                    }
                }
            }
        }

        return $montoActivo;
    }

    public function calcularFidelizacion(Carbon $inicio, Carbon $fin)
    {
        $detalle = $this->calcularFidelizacionDetalle($inicio, $fin);

        return $detalle['porcentaje'];
    }

    public function calcularFidelizacionDetalle(Carbon $inicio, Carbon $fin): array
    {
        $inicioDate = $inicio->copy()->startOfDay()->toDateString();
        $finDate = $fin->copy()->endOfDay()->toDateString();

        // Subconsulta: préstamos liquidados por fecha de último pago en el rango.
        $liquidados = DB::table('prestamos as p')
            ->join('pagos as pa', 'pa.prestamo_id', '=', 'p.id')
            ->whereIn('p.estado', ['Pagado', 'Liquidado'])
            ->groupBy('p.id', 'p.cliente_id')
            ->havingRaw('MAX(pa.fecha_pago) BETWEEN ? AND ?', [$inicioDate, $finDate])
            ->selectRaw('p.id as prestamo_liquidado_id, p.cliente_id, MAX(pa.fecha_pago) as fecha_liquidacion');

        $totalLiquidados = DB::query()
            ->fromSub($liquidados, 'l')
            ->distinct('l.cliente_id')
            ->count('l.cliente_id');

        if ($totalLiquidados === 0) {
            return [
                'porcentaje' => 0.0,
                'liquidados' => 0,
                'renovados' => 0,
            ];
        }

        // Un cliente cuenta como renovado si tiene al menos un préstamo nuevo
        // desde la liquidación y hasta el fin de ese mismo mes (acotado por $fin).
        $clientesRenovados = DB::query()
            ->fromSub($liquidados, 'l')
            ->join('prestamos as r', function ($join) {
                $join->on('r.cliente_id', '=', 'l.cliente_id')
                    ->on('r.id', '<>', 'l.prestamo_liquidado_id');
            })
            ->whereIn('r.estado', ['Entregado', 'Atrasado', 'Pagado', 'Liquidado'])
            ->whereRaw('DATE(r.fecha_entrega) >= DATE(l.fecha_liquidacion)')
            ->whereRaw('DATE(r.fecha_entrega) <= LEAST(LAST_DAY(DATE(l.fecha_liquidacion)), ?)', [$finDate])
            ->distinct('l.cliente_id')
            ->count('l.cliente_id');

        return [
            'porcentaje' => min(100, round(($clientesRenovados / $totalLiquidados) * 100, 2)),
            'liquidados' => $totalLiquidados,
            'renovados' => $clientesRenovados,
        ];
    }
}
