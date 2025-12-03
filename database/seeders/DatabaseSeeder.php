<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Primero ejecutar el seeder de roles y permisos
        $this->call(RoleAndPermissionSeeder::class);

        // Luego los otros seeders
        $this->call([
            // CapitalSeeder::class,
            /*  ClientSeeder::class,
            LoanSeeder::class,
            PaymentSeeder::class, */
            \Database\Seeders\ClienteSeeder::class,
        ]);
    }
}
