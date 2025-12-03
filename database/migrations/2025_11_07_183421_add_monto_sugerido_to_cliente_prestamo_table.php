<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cliente_prestamo', function (Blueprint $table) {
            // Solo agregar la columna si no existe
            if (! Schema::hasColumn('cliente_prestamo', 'monto_sugerido')) {
                $table->decimal('monto_sugerido', 10, 2)->nullable()->after('monto_solicitado');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cliente_prestamo', function (Blueprint $table) {
            if (Schema::hasColumn('cliente_prestamo', 'monto_sugerido')) {
                $table->dropColumn('monto_sugerido');
            }
        });
    }
};
