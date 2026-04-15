<?php

namespace App\Services;

use Carbon\Carbon;

class CalculadoraPrestamos
{
    /**
     * Función para calcular el calendario de pagos según las reglas de negocio específicas
     */
    public static function calcularCalendarioPagos($monto, $tasaInteres, $plazo, $periodicidad, $fechaPrimerPago, $ultimoPago = null, $diaPago = 'martes')
    {
        $plazoNormalizado = strtolower(trim($plazo));
        $periodicidadNormalizada = strtolower(trim($periodicidad));

        $config = self::determinarConfiguracionPago($plazoNormalizado, $periodicidadNormalizada);

        if (! $config) {
            return self::calcularCalendarioBasico($monto, $tasaInteres, $plazo, $periodicidad, $fechaPrimerPago, $diaPago);
        }

        // Calcular monto total usando la misma lógica que el calendario
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
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        // Determinar intervalo en días según periodicidad
        $intervaloDias = match (strtolower($periodicidadNormalizada)) {
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
                'fecha' => $fechaPago->format('Y-m-d'), // Change to Y-m-d for easier filtering
                'fecha_format' => $fechaPago->format('d-m-Y'),
                'monto' => $montoPago,
                'total_pagos' => $numeroPagos,
            ];

            $fechaActual = $fechaPago->copy();
        }

        return $calendario;
    }

    private static function determinarConfiguracionPago($plazo, $periodicidad)
    {
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

        $clave = $plazo.'_'.$periodicidad;

        return $configuraciones[$clave] ?? null;
    }

    private static function calcularCalendarioBasico($monto, $tasaInteres, $plazo, $periodicidad, $fechaPrimerPago, $diaPago = 'martes')
    {
        $plazoNumerico = preg_replace('/[^0-9]/', '', $plazo);
        $plazoNumerico = current(explode(' ', $plazoNumerico)) ?: 1;
        $periodicidadNormalizada = strtolower(trim($periodicidad));

        $esSemanal = in_array($periodicidadNormalizada, ['semanal', 'semana', 'weekly']);
        $esQuincenal = in_array($periodicidadNormalizada, ['quincenal', 'quincena', 'biweekly', 'catorcenal']);
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
        $montoTotal = $montoTotal + ($montoTotal * (\App\Models\Configuration::get('iva_percentage', 16) / 100));
        $montoPorPagoBase = floor($montoTotal / $numeroPagos);
        $decimales = ($montoTotal / $numeroPagos) - $montoPorPagoBase;
        $montoUltimoPago = $montoPorPagoBase + ($decimales * $numeroPagos);

        $calendario = [];
        $fechaActual = Carbon::parse($fechaPrimerPago);

        for ($i = 1; $i <= $numeroPagos; $i++) {
            $montoPago = ($i === $numeroPagos) ? $montoUltimoPago : $montoPorPagoBase;
            $calendario[] = [
                'numero' => $i,
                'fecha' => $fechaActual->format('Y-m-d'),
                'fecha_format' => $fechaActual->format('d-m-Y'),
                'monto' => $montoPago,
                'total_pagos' => $numeroPagos,
            ];
            $fechaActual->addDays($intervaloDias);
        }

        return $calendario;
    }
}
