<?php

namespace Tests\Unit;

use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Prestamo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagoTest extends TestCase
{
    use RefreshDatabase;

    public function test_pago_pertenece_a_prestamo(): void
    {
        $pago = Pago::factory()->create();

        $this->assertInstanceOf(Prestamo::class, $pago->prestamo);
    }

    public function test_pago_pertenece_a_cliente(): void
    {
        $pago = Pago::factory()->create();

        $this->assertInstanceOf(Cliente::class, $pago->cliente);
    }

    public function test_pago_pertenece_a_registrador(): void
    {
        $pago = Pago::factory()->create();

        $this->assertInstanceOf(User::class, $pago->registrador);
    }

    public function test_puede_calcular_total_efectivo_desde_desglose(): void
    {
        $pago = Pago::factory()->create([
            'desglose_efectivo' => [
                'billetes' => [
                    '1000' => 2,  // 2000
                    '500' => 1,   // 500
                    '100' => 3,   // 300
                ],
                'monedas' => [
                    '10' => 5,    // 50
                    '5' => 2,     // 10
                ],
            ],
        ]);

        $total = $pago->calcularTotalEfectivo();

        $this->assertEquals(2860, $total);
    }

    public function test_calcula_cero_cuando_no_hay_desglose(): void
    {
        $pago = Pago::factory()->create([
            'desglose_efectivo' => null,
        ]);

        $total = $pago->calcularTotalEfectivo();

        $this->assertEquals(0, $total);
    }

    public function test_calcula_cero_cuando_desglose_esta_vacio(): void
    {
        $pago = Pago::factory()->create([
            'desglose_efectivo' => [
                'billetes' => [],
                'monedas' => [],
            ],
        ]);

        $total = $pago->calcularTotalEfectivo();

        $this->assertEquals(0, $total);
    }

    public function test_desglose_efectivo_se_guarda_como_array(): void
    {
        $desglose = [
            'billetes' => [
                '1000' => 5,
                '500' => 3,
            ],
            'monedas' => [
                '10' => 10,
            ],
        ];

        $pago = Pago::factory()->create([
            'desglose_efectivo' => $desglose,
        ]);

        $this->assertIsArray($pago->desglose_efectivo);
        $this->assertEquals(5, $pago->desglose_efectivo['billetes']['1000']);
        $this->assertEquals(3, $pago->desglose_efectivo['billetes']['500']);
        $this->assertEquals(10, $pago->desglose_efectivo['monedas']['10']);
    }

    public function test_campos_monetarios_se_castean_correctamente(): void
    {
        $pago = Pago::factory()->create([
            'monto' => '1234.56',
            'capital_pagado' => '800.00',
            'interes_pagado' => '434.56',
        ]);

        $this->assertIsString($pago->monto);
        $this->assertEquals('1234.56', $pago->monto);
        $this->assertEquals('800.00', $pago->capital_pagado);
        $this->assertEquals('434.56', $pago->interes_pagado);
    }

    public function test_fecha_pago_se_castea_a_date(): void
    {
        $pago = Pago::factory()->create([
            'fecha_pago' => '2025-12-01',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $pago->fecha_pago);
        $this->assertEquals('2025-12-01', $pago->fecha_pago->format('Y-m-d'));
    }
}
