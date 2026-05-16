<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Prestamo;
use App\Models\User;
use Carbon\Carbon;

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
                ->with('pagos');
        }])->get();

        $resultados = [];
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

            $clientesAsesor = [];

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

                if ($bucket !== 'cv_mas_365') {
                    $dataAsesor['saldo_total'] += $saldoRestante;

                    if (! isset($clientesAsesor[$prestamo->cliente_id])) {
                        $clientesAsesor[$prestamo->cliente_id] = true;
                        $dataAsesor['clientes'] += 1;
                        $dataAsesor[$bucket]['clientes'] += 1; // Simplificacion, asignamos el cliente al su primer prestamo evaluado
                    }
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
        ];
    }
}
