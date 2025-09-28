<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hacer periodo_pago nullable para evitar conflictos cuando la app usa 'periodicidad'
        DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `periodo_pago` VARCHAR(191) NULL;");
    }

    public function down(): void
    {
        // Revertir a NOT NULL por seguridad
        DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `periodo_pago` VARCHAR(191) NOT NULL;");
    }
};
