<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Asegurar que columnas opcionales sean nullable
        DB::statement('ALTER TABLE `prestamos` MODIFY COLUMN `cliente_id` BIGINT UNSIGNED NULL;');
        DB::statement('ALTER TABLE `prestamos` MODIFY COLUMN `monto_total` DECIMAL(12,2) NULL;');
        DB::statement('ALTER TABLE `prestamos` MODIFY COLUMN `fecha_primer_pago` DATE NULL;');
        DB::statement('ALTER TABLE `prestamos` MODIFY COLUMN `periodo_pago` VARCHAR(191) NULL;');
        DB::statement('ALTER TABLE `prestamos` MODIFY COLUMN `dia_pago` VARCHAR(191) NULL;');
        // grupo_id no existe si no es necesario; si existe hacerlo nullable
        try {
            DB::statement('ALTER TABLE `prestamos` MODIFY COLUMN `grupo_id` BIGINT UNSIGNED NULL;');
        } catch (\Exception $e) {
            // columna puede no existir en todas las instalaciones
        }
    }

    public function down(): void
    {
        // Revertir a NOT NULL donde sea razonable (no forzamos a mantener constraints)
        try {
            DB::statement('ALTER TABLE `prestamos` MODIFY COLUMN `cliente_id` BIGINT UNSIGNED NOT NULL;');
        } catch (\Exception $e) {
        }
        try {
            DB::statement('ALTER TABLE `prestamos` MODIFY COLUMN `monto_total` DECIMAL(12,2) NOT NULL;');
        } catch (\Exception $e) {
        }
        try {
            DB::statement('ALTER TABLE `prestamos` MODIFY COLUMN `fecha_primer_pago` DATE NOT NULL;');
        } catch (\Exception $e) {
        }
        try {
            DB::statement('ALTER TABLE `prestamos` MODIFY COLUMN `periodo_pago` VARCHAR(191) NOT NULL;');
        } catch (\Exception $e) {
        }
        try {
            DB::statement('ALTER TABLE `prestamos` MODIFY COLUMN `dia_pago` VARCHAR(191) NOT NULL;');
        } catch (\Exception $e) {
        }
        try {
            DB::statement('ALTER TABLE `prestamos` MODIFY COLUMN `grupo_id` BIGINT UNSIGNED NOT NULL;');
        } catch (\Exception $e) {
        }
    }
};
