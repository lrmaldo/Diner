<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class ShowRolesAndPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'show:roles-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Muestra los roles, permisos y usuarios con sus roles asignados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Roles:');
        $roles = Role::all();

        if ($roles->isEmpty()) {
            $this->warn('No hay roles registrados');
        } else {
            foreach ($roles as $role) {
                $this->line(" - {$role->name}");
                $this->info('   Permisos:');

                $perms = $role->permissions()->get();
                if ($perms->isEmpty()) {
                    $this->warn('     No tiene permisos asignados');
                } else {
                    foreach ($perms as $perm) {
                        $this->line("     * {$perm->name}");
                    }
                }
            }
        }

        $this->newLine();
        $this->info('Permisos:');
        $permissions = Permission::all();

        if ($permissions->isEmpty()) {
            $this->warn('No hay permisos registrados');
        } else {
            foreach ($permissions as $permission) {
                $this->line(" - {$permission->name}");
            }
        }

        $this->newLine();
        $this->info('Usuarios con roles:');
        $users = User::all();

        if ($users->isEmpty()) {
            $this->warn('No hay usuarios registrados');
        } else {
            foreach ($users as $user) {
                $this->line(" - {$user->name} ({$user->email})");
                $this->info('   Roles:');

                $userRoles = $user->roles()->get();
                if ($userRoles->isEmpty()) {
                    $this->warn('     No tiene roles asignados');
                } else {
                    foreach ($userRoles as $role) {
                        $this->line("     * {$role->name}");
                    }
                }
            }
        }
    }
}
