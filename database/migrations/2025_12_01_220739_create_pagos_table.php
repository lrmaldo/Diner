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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestamo_id')->constrained('prestamos')->onDelete('cascade');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->foreignId('registrado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('monto', 10, 2);
            $table->date('fecha_pago');
            $table->string('tipo_pago')->default('abono'); // abono, pago_completo, liquidacion
            $table->integer('numero_pago')->nullable(); // número de pago en la secuencia
            $table->decimal('saldo_anterior', 10, 2)->nullable();
            $table->decimal('saldo_nuevo', 10, 2)->nullable();
            $table->decimal('interes_pagado', 10, 2)->default(0);
            $table->decimal('capital_pagado', 10, 2)->default(0);
            $table->decimal('moratorio_pagado', 10, 2)->default(0);
            $table->text('notas')->nullable();
            // Desglose de efectivo recibido
            $table->json('desglose_efectivo')->nullable(); // {"billetes": {"1000": 9, "500": 4}, "monedas": {}}
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
