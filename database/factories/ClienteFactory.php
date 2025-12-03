<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'apellido_paterno' => $this->faker->lastName(),
            'apellido_materno' => $this->faker->lastName(),
            'nombres' => $this->faker->firstName(),
            'curp' => strtoupper($this->faker->bothify('????######???###')),
            'email' => $this->faker->unique()->safeEmail(),
            'pais_nacimiento' => 'México',
            'nombre_conyuge' => $this->faker->name(),
            'calle_numero' => $this->faker->streetAddress(),
            'referencia_domiciliaria' => $this->faker->sentence(),
            'estado_civil' => $this->faker->randomElement(['soltero', 'casado', 'divorciado', 'viudo']),
            'dependientes_economicos' => $this->faker->numberBetween(0, 5),
            'nombre_aval' => $this->faker->name(),
            'actividad_productiva' => $this->faker->randomElement(['Comercio', 'Agricultura', 'Servicios', 'Manufactura']),
            'anios_experiencia' => $this->faker->numberBetween(1, 20),
            'ingreso_mensual' => $this->faker->randomFloat(2, 5000, 25000),
            'gasto_mensual_familiar' => $this->faker->randomFloat(2, 3000, 15000),
            'credito_solicitado' => $this->faker->randomFloat(2, 5000, 50000),
            'estado' => $this->faker->randomElement(['Campeche', 'Yucatán', 'Quintana Roo']),
            'municipio' => $this->faker->city(),
            'colonia' => $this->faker->streetName(),
            'codigo_postal' => $this->faker->postcode(),
        ];
    }
}
