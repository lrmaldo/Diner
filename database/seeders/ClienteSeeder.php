<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Telefono;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $cliente = Cliente::create([
            'apellido_paterno' => 'García',
            'apellido_materno' => 'López',
            'nombres' => 'María Fernanda',
            'curp' => 'GAML950101HDFRRS09',
            'email' => 'maria@example.com',
            'pais_nacimiento' => 'México',
            'nombre_conyuge' => null,
            'calle_numero' => 'Av. Reforma 123',
            'referencia_domiciliaria' => 'Entre 1 y 2',
            'estado_civil' => 'Soltera',
            'dependientes_economicos' => 0,
            'nombre_aval' => null,
            'actividad_productiva' => 'Comercio',
            'anios_experiencia' => 5,
            'ingreso_mensual' => 12000.00,
            'gasto_mensual_familiar' => 6000.00,
            'credito_solicitado' => 50000.00,
            'estado' => 'Ciudad de México',
            'municipio' => 'Cuauhtémoc',
            'colonia' => 'Juárez',
            'codigo_postal' => '06600',
        ]);

        Telefono::create([
            'cliente_id' => $cliente->id,
            'tipo' => 'celular',
            'numero' => '5540012345',
        ]);

        Telefono::create([
            'cliente_id' => $cliente->id,
            'tipo' => 'casa',
            'numero' => '5555123456',
        ]);
    }
}
