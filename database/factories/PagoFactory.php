<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pago>
 */
class PagoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $monto = $this->faker->randomFloat(2, 100, 5000);

        return [
            'prestamo_id' => \App\Models\Prestamo::factory(),
            'cliente_id' => \App\Models\Cliente::factory(),
            'registrado_por' => \App\Models\User::factory(),
            'monto' => $monto,
            'fecha_pago' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'tipo_pago' => $this->faker->randomElement(['abono', 'pago_completo', 'liquidacion']),
            'numero_pago' => $this->faker->numberBetween(1, 12),
            'saldo_anterior' => $this->faker->randomFloat(2, 0, 10000),
            'saldo_nuevo' => $this->faker->randomFloat(2, 0, 10000),
            'interes_pagado' => $monto * 0.3,
            'capital_pagado' => $monto * 0.7,
            'moratorio_pagado' => 0,
            'notas' => $this->faker->optional()->sentence(),
            'desglose_efectivo' => [
                'billetes' => [
                    '1000' => $this->faker->numberBetween(0, 2),
                    '500' => $this->faker->numberBetween(0, 3),
                    '200' => $this->faker->numberBetween(0, 5),
                    '100' => $this->faker->numberBetween(0, 10),
                ],
                'monedas' => [
                    '10' => $this->faker->numberBetween(0, 5),
                    '5' => $this->faker->numberBetween(0, 10),
                ],
            ],
        ];
    }
}
