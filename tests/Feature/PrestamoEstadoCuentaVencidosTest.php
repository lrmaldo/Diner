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

        // Exigible hasta hoy (<= 05-01-26): 3 pagos => 300
        $this->assertSame(0.0, Prestamo::calcularMontoVencidoDesdeCalendario($calendario, $fechaHoy, 300));
        $this->assertSame(0.0, Prestamo::calcularMontoVencidoDesdeCalendario($calendario, $fechaHoy, 450));
        $this->assertSame(150.0, Prestamo::calcularMontoVencidoDesdeCalendario($calendario, $fechaHoy, 150));

        // Atrasos (fecha < hoy) con pagos acumulados aplicados al vencido primero
        // Con totalPagado=150: a 01/01 cubre, a 02/01 ya no cubre el acumulado 200, a 03/01 tampoco (300)
        $this->assertSame(2, Prestamo::calcularAtrasosDesdeCalendario($calendario, $fechaHoy, 150, 1));
        $this->assertSame(0, Prestamo::calcularAtrasosDesdeCalendario($calendario, $fechaHoy, 300, 1));
    }
}
