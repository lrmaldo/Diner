<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change plazo to varchar to support labels like '4meses'
        DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `plazo` VARCHAR(50) NULL;");
    }

    public function down(): void
    {
        // revert to smallint unsigned
        DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `plazo` SMALLINT UNSIGNED NULL;");
    }
};
