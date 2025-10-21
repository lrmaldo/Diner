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
        Schema::table('prestamos', function (Blueprint $table) {
            $table->unsignedBigInteger('asesor_id')->nullable()->after('representante_id');
            $table->decimal('garantia', 5, 2)->default(10.00)->after('tasa_interes');

            $table->foreign('asesor_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropForeign(['asesor_id']);
            $table->dropColumn(['asesor_id', 'garantia']);
        });
    }
};
