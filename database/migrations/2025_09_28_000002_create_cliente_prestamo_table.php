<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_prestamo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('prestamo_id')->constrained('prestamos')->cascadeOnDelete();
            $table->decimal('monto_solicitado', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['cliente_id', 'prestamo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_prestamo');
    }
};
