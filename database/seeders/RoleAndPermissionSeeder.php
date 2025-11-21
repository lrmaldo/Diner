<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpia la caché de roles y permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos para usuarios
        $userPermissions = [
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'eliminar usuarios',
        ];

        // Crear permisos para clientes
        $clientPermissions = [
            'ver clientes',
            'crear clientes',
            'editar clientes',
            'eliminar clientes',
        ];

        // Crear permisos para préstamos
        $loanPermissions = [
            'ver prestamos',
            'crear prestamos',
            'editar prestamos',
            'eliminar prestamos',
            'aprobar prestamos',
        ];

        // Crear permisos para pagos
        $paymentPermissions = [
            'ver pagos',
            'registrar pagos',
            'anular pagos',
        ];

        // Crear permisos para capital
        $capitalPermissions = [
            'ver capital',
            'registrar capital',
            'editar capital',
        ];

        // Crear permisos para informes
        $reportPermissions = [
            'ver informes',
            'exportar informes',
        ];

        // Crear permisos para administración del sistema
        $systemPermissions = [
            'administrar sistema',
        ];

        // Combinar todos los permisos
        $allPermissions = array_merge(
            $userPermissions,
            $clientPermissions,
            $loanPermissions,
            $paymentPermissions,
            $capitalPermissions,
            $reportPermissions,
            $systemPermissions
        );

        // Crear todos los permisos en la base de datos
        foreach ($allPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear rol de Administrador con todos los permisos
        $adminRole = Role::create(['name' => 'Administrador']);
        $adminRole->givePermissionTo(Permission::all());

        // Crear rol de Cajero con permisos limitados
        $cashierRole = Role::create(['name' => 'Cajero']);
        $cashierPerms = array_merge(
            ['ver usuarios'],
            $clientPermissions,
            ['ver prestamos', 'crear prestamos'],
            $paymentPermissions,
            ['ver capital'],
            ['ver informes']
        );
        $cashierRole->givePermissionTo($cashierPerms);

        // Crear rol de Asesor (rol para gestión comercial / préstamos)
        // Se crea solo si no existe, y se le asignan permisos básicos relacionados con clientes y préstamos.
        $advisorRole = Role::firstOrCreate(['name' => 'Asesor']);
        $advisorPerms = [
            'ver clientes',
            'crear clientes',
            'ver prestamos',
            'crear prestamos',
            'ver informes',
        ];
        $advisorRole->givePermissionTo($advisorPerms);

        // Crear un usuario administrador por defecto
        $admin = User::where('email', 'admin@diner.com')->first();

        if (!$admin) {
            $admin = User::factory()->create([
                'name' => 'Administrador',
                'email' => 'admin@diner.com',
                'password' => bcrypt('password')
            ]);
        }

        $admin->assignRole('Administrador');

        // Crear un usuario cajero por defecto
        $cashier = User::where('email', 'cajero@diner.com')->first();

        if (!$cashier) {
            $cashier = User::factory()->create([
                'name' => 'Cajero',
                'email' => 'cajero@diner.com',
                'password' => bcrypt('password')
            ]);
        }

        $cashier->assignRole('Cajero');

        // crear un usuario asesor por defecto
        $asesor = User::where('email', 'asesor@diner.com')->first();
        if (! $asesor) {
            $asesor = User::factory()->create([
                'name' => 'Asesor',
                'email' => 'asesor@diner.com',
                'password' => bcrypt('password')
            ]);
        }

        $asesor->assignRole('Asesor');
    }
}
