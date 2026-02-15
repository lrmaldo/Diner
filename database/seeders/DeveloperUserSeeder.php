<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DeveloperUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asumiendo que el rol 'Administrador' ya existe, lo buscamos.
        // Si no existe, deberíamos crearlo o manejar el error, pero
        // dado el contexto del proyecto, 'Administrador' ya debe existir.
        
        $developerEmail = 'lrmaldo@gmail.com';

        $user = User::firstOrCreate(
            ['email' => $developerEmail],
            [
                'name' => 'Desarrollador',
                'password' => Hash::make('secret'),
                'email_verified_at' => now(),
            ]
        );

        // Asegurar que tenga el rol de Administrador
        if (!$user->hasRole('Administrador')) {
            $user->assignRole('Administrador');
        }

        $this->command->info("Usuario desarrollador creado: {$developerEmail}");
    }
}
