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
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('metodo_pago')->default('efectivo')->after('tipo_pago');
        });

        Schema::table('capitalizaciones', function (Blueprint $table) {
            $table->string('origen_fondos')->default('externo')->after('monto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn('metodo_pago');
        });

        Schema::table('capitalizaciones', function (Blueprint $table) {
            $table->dropColumn('origen_fondos');
        });
    }
};
