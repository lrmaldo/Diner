<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->morphs('prestamoable');
            // monto removed: handled via monto_total or per-cliente pivot
            // $table->decimal('monto', 15, 2);
            $table->string('plazo');
            $table->string('periodo_pago')->nullable();
            $table->string('dia_pago')->nullable();
            $table->date('fecha_entrega');
            $table->decimal('tasa_interes', 8, 4)->default(4.5);
            $table->string('estado')->default('en_curso');
            $table->foreignId('autorizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
