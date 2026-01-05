<?php

namespace Tests\Feature;

use App\Models\Prestamo;
use Carbon\Carbon;
use Tests\TestCase;

class PrestamoEstadoCuentaVencidosTest extends TestCase
{
    public function test_descuenta_pagos_en_saldos_vencidos_sin_depender_de_numero_pago(): void
    {
        $fechaHoy = Carbon::create(2026, 1, 5)->startOfDay();

        $calendario = [
            ['numero' => 1, 'fecha' => '01-01-26', 'monto' => 100],
            ['numero' => 2, 'fecha' => '02-01-26', 'monto' => 100],
            ['numero' => 3, 'fecha' => '03-01-26', 'monto' => 100],
            ['numero' => 4, 'fecha' => '10-01-26', 'monto' => 100],
        ];

        // Hasta hoy (<= 05-01-26) hay 3 cuotas (300). Pagos por numero: cuota 1 pagada.
        // Pagos sin numero (FIFO): 50 se aplica a cuota 2.
        $pagadoPorNumero = [
            1 => 100,
        ];

        $this->assertSame(150.0, Prestamo::calcularMontoVencidoDesdeCalendario($calendario, $fechaHoy, $pagadoPorNumero, 50));

        // Atrasos (fecha < hoy): cuotas 1-3.
        // cuota1 ok, cuota2 queda con 50/100, cuota3 0/100 => 2 atrasos.
        $this->assertSame(2, Prestamo::calcularAtrasosDesdeCalendario($calendario, $fechaHoy, $pagadoPorNumero, 50, 1));

        // Si se cubre todo lo vencido con pagos sin numero, vencido y atrasos bajan a 0.
        $this->assertSame(0.0, Prestamo::calcularMontoVencidoDesdeCalendario($calendario, $fechaHoy, $pagadoPorNumero, 200));
        $this->assertSame(0, Prestamo::calcularAtrasosDesdeCalendario($calendario, $fechaHoy, $pagadoPorNumero, 200, 1));
    }
}
