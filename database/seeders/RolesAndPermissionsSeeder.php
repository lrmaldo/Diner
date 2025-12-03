<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Resetear caché de permisos
        app()['cache']->forget('spatie.permission.cache');

        // Crear permisos para usuarios
        $userPermissions = [
            'users.viewAny' => 'Ver lista de usuarios',
            'users.view' => 'Ver detalles de usuario',
            'users.create' => 'Crear usuarios',
            'users.update' => 'Actualizar usuarios',
            'users.delete' => 'Eliminar usuarios',
        ];

        // Crear permisos para clientes
        $clientPermissions = [
            'clients.viewAny' => 'Ver lista de clientes',
            'clients.view' => 'Ver detalles de cliente',
            'clients.create' => 'Crear clientes',
            'clients.update' => 'Actualizar clientes',
            'clients.delete' => 'Eliminar clientes',
        ];

        // Crear permisos para préstamos
        $loanPermissions = [
            'loans.viewAny' => 'Ver lista de préstamos',
            'loans.view' => 'Ver detalles de préstamo',
            'loans.create' => 'Crear préstamos',
            'loans.update' => 'Actualizar préstamos',
            'loans.delete' => 'Eliminar préstamos',
        ];

        // Crear permisos para pagos
        $paymentPermissions = [
            'payments.viewAny' => 'Ver lista de pagos',
            'payments.view' => 'Ver detalles de pago',
            'payments.create' => 'Crear pagos',
            'payments.update' => 'Actualizar pagos',
            'payments.delete' => 'Eliminar pagos',
        ];

        // Crear permisos para capital
        $capitalPermissions = [
            'capital.viewAny' => 'Ver lista de movimientos de capital',
            'capital.view' => 'Ver detalles de capital',
            'capital.create' => 'Registrar movimientos de capital',
            'capital.update' => 'Actualizar movimientos de capital',
            'capital.delete' => 'Eliminar movimientos de capital',
        ];

        // Crear permisos para configuración
        $settingPermissions = [
            'settings.view' => 'Ver configuración',
            'settings.update' => 'Actualizar configuración',
        ];

        // Juntar todos los permisos
        $allPermissions = array_merge(
            $userPermissions,
            $clientPermissions,
            $loanPermissions,
            $paymentPermissions,
            $capitalPermissions,
            $settingPermissions
        );

        // Crear los permisos en la base de datos
        foreach ($allPermissions as $name => $description) {
            Permission::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'guard_name' => 'web',
            ]);
        }

        // Crear rol de Administrador
        $adminRole = Role::create([
            'name' => 'Administrador',
            'slug' => 'administrador',
            'guard_name' => 'web',
        ]);

        // Asignar todos los permisos al rol Administrador
        $adminRole->givePermissionTo(array_keys($allPermissions));

        // Crear rol de Cajero
        $cajeroRole = Role::create([
            'name' => 'Cajero',
            'slug' => 'cajero',
            'guard_name' => 'web',
        ]);

        // Asignar permisos limitados al rol Cajero
        $cajeroRole->givePermissionTo([
            // Cliente: solo vista y creación
            'clients.viewAny',
            'clients.view',
            'clients.create',

            // Préstamos: solo vista y creación
            'loans.viewAny',
            'loans.view',
            'loans.create',

            // Pagos: todos los permisos
            'payments.viewAny',
            'payments.view',
            'payments.create',
            'payments.update',

            // Capital: solo vista
            'capital.viewAny',
            'capital.view',
        ]);

        // Crear un usuario administrador
        $adminUser = User::create([
            'name' => 'Administrador',
            'email' => 'admin@ejemplo.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'Administrador',
        ]);

        // Asignar rol de administrador al usuario
        $adminUser->assignRole('Administrador');

        // Crear un usuario cajero
        $cajeroUser = User::create([
            'name' => 'Cajero',
            'email' => 'cajero@ejemplo.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'Cajero',
        ]);

        // Asignar rol de cajero al usuario
        $cajeroUser->assignRole('Cajero');
    }
}
