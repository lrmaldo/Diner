<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Prestamo>
 */
class PrestamoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $producto = $this->faker->randomElement(['individual', 'grupal']);
        $plazo = $this->faker->randomElement([4, 8, 12, 16, 20]);
        $montoTotal = $this->faker->randomFloat(2, 5000, 50000);

        return [
            'folio' => 'PRE-'.$this->faker->unique()->numerify('########'),
            'producto' => $producto,
            'monto_total' => $montoTotal,
            'monto_sugerido' => $montoTotal,
            'plazo' => $plazo,
            'periodicidad' => $this->faker->randomElement(['semanal', 'quincenal', 'mensual']),
            'periodo_pago' => $this->faker->randomElement(['semanal', 'quincenal', 'mensual']),
            'dia_pago' => $this->faker->randomElement(['lunes', 'martes', 'miércoles', 'jueves', 'viernes']),
            'fecha_entrega' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'fecha_primer_pago' => $this->faker->dateTimeBetween('now', '+1 month'),
            'tasa_interes' => $this->faker->randomFloat(2, 10, 30),
            'garantia' => $this->faker->optional()->randomFloat(2, 0, 5000),
            'estado' => $this->faker->randomElement(['pendiente', 'en_comite', 'autorizado', 'rechazado']),
            'autorizado_por' => null,
            'cliente_id' => $producto === 'individual' ? \App\Models\Cliente::factory() : null,
            'grupo_id' => $producto === 'grupal' ? \App\Models\Grupo::factory() : null,
            'representante_id' => $producto === 'grupal' ? \App\Models\Cliente::factory() : null,
            'asesor_id' => \App\Models\User::factory(),
            'motivo_rechazo' => null,
            'comentarios_comite' => null,
        ];
    }
}
