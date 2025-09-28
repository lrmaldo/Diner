<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // If the original 'monto' column exists, drop it. Use raw statement to be safe across DBs.
        if (Schema::hasTable('prestamos')) {
            if (Schema::hasColumn('prestamos', 'monto')) {
                Schema::table('prestamos', function (Blueprint $table) {
                    $table->dropColumn('monto');
                });
            }

            // Make prestamoable columns nullable
            DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `prestamoable_type` VARCHAR(191) NULL;");
            DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `prestamoable_id` BIGINT UNSIGNED NULL;");

            // cliente_id
            if (! Schema::hasColumn('prestamos', 'cliente_id')) {
                Schema::table('prestamos', function (Blueprint $table) {
                    $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
                });
            } else {
                DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `cliente_id` BIGINT UNSIGNED NULL;");
            }

            // grupo_id (optional)
            if (! Schema::hasColumn('prestamos', 'grupo_id')) {
                Schema::table('prestamos', function (Blueprint $table) {
                    $table->foreignId('grupo_id')->nullable()->constrained('grupos')->nullOnDelete();
                });
            } else {
                DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `grupo_id` BIGINT UNSIGNED NULL;");
            }

            // folio
            if (! Schema::hasColumn('prestamos', 'folio')) {
                Schema::table('prestamos', function (Blueprint $table) {
                    $table->string('folio')->unique()->nullable()->after('id');
                });
            } else {
                DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `folio` VARCHAR(255) NULL;");
            }

            // producto
            if (! Schema::hasColumn('prestamos', 'producto')) {
                Schema::table('prestamos', function (Blueprint $table) {
                    $table->string('producto')->nullable()->after('folio');
                });
            } else {
                DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `producto` VARCHAR(191) NULL;");
            }

            // plazo as string
            if (! Schema::hasColumn('prestamos', 'plazo')) {
                Schema::table('prestamos', function (Blueprint $table) {
                    $table->string('plazo')->nullable()->after('producto');
                });
            } else {
                // if numeric, alter to varchar nullable
                DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `plazo` VARCHAR(191) NULL;");
            }

            // periodicidad
            if (! Schema::hasColumn('prestamos', 'periodicidad')) {
                Schema::table('prestamos', function (Blueprint $table) {
                    $table->string('periodicidad')->nullable()->after('plazo');
                });
            } else {
                DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `periodicidad` VARCHAR(191) NULL;");
            }

            // fecha_primer_pago
            if (! Schema::hasColumn('prestamos', 'fecha_primer_pago')) {
                Schema::table('prestamos', function (Blueprint $table) {
                    $table->date('fecha_primer_pago')->nullable()->after('fecha_entrega');
                });
            } else {
                DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `fecha_primer_pago` DATE NULL;");
            }

            // monto_total
            if (! Schema::hasColumn('prestamos', 'monto_total')) {
                Schema::table('prestamos', function (Blueprint $table) {
                    $table->decimal('monto_total', 12, 2)->nullable()->after('tasa_interes');
                });
            } else {
                DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `monto_total` DECIMAL(12,2) NULL;");
            }

            // dia_pago
            if (! Schema::hasColumn('prestamos', 'dia_pago')) {
                Schema::table('prestamos', function (Blueprint $table) {
                    $table->string('dia_pago')->nullable();
                });
            } else {
                DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `dia_pago` VARCHAR(191) NULL;");
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('prestamos')) {
            // Try to restore monto as nullable decimal (best-effort)
            if (! Schema::hasColumn('prestamos', 'monto')) {
                Schema::table('prestamos', function (Blueprint $table) {
                    $table->decimal('monto', 15, 2)->nullable()->after('prestamoable_id');
                });
            }

            // Revert prestamoable columns to NOT NULL
            DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `prestamoable_type` VARCHAR(191) NOT NULL;");
            DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `prestamoable_id` BIGINT UNSIGNED NOT NULL;");

            // cliente_id
            if (Schema::hasColumn('prestamos', 'cliente_id')) {
                DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `cliente_id` BIGINT UNSIGNED NOT NULL;");
            }

            // grupo_id
            if (Schema::hasColumn('prestamos', 'grupo_id')) {
                DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `grupo_id` BIGINT UNSIGNED NOT NULL;");
            }

            // folio
            if (Schema::hasColumn('prestamos', 'folio')) {
                DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `folio` VARCHAR(255) NOT NULL;");
            }

            // producto
            if (Schema::hasColumn('prestamos', 'producto')) {
                DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `producto` VARCHAR(191) NOT NULL;");
            }

            // plazo
            if (Schema::hasColumn('prestamos', 'plazo')) {
                DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `plazo` INT UNSIGNED NOT NULL;");
            }

            // periodicidad
            if (Schema::hasColumn('prestamos', 'periodicidad')) {
                DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `periodicidad` VARCHAR(191) NOT NULL;");
            }

            // fecha_primer_pago
            if (Schema::hasColumn('prestamos', 'fecha_primer_pago')) {
                DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `fecha_primer_pago` DATE NOT NULL;");
            }

            // monto_total
            if (Schema::hasColumn('prestamos', 'monto_total')) {
                DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `monto_total` DECIMAL(12,2) NOT NULL;");
            }

            // dia_pago
            if (Schema::hasColumn('prestamos', 'dia_pago')) {
                DB::statement("ALTER TABLE `prestamos` MODIFY COLUMN `dia_pago` VARCHAR(191) NOT NULL;");
            }
        }
    }
};
