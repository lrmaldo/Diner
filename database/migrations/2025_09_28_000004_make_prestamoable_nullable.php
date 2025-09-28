<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `prestamoable_type` VARCHAR(191) NULL;");
        DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `prestamoable_id` BIGINT UNSIGNED NULL;");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `prestamoable_type` VARCHAR(191) NOT NULL;");
        DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `prestamoable_id` BIGINT UNSIGNED NOT NULL;");
    }
};
