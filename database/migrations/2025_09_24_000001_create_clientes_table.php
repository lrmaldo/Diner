<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('apellido_paterno');
            $table->string('apellido_materno')->nullable();
            $table->string('nombres');
            $table->string('curp')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('pais_nacimiento')->nullable();
            $table->string('nombre_conyuge')->nullable();
            $table->string('calle_numero');
            $table->text('referencia_domiciliaria')->nullable();
            $table->string('estado_civil')->nullable();
            $table->integer('dependientes_economicos')->default(0);
            $table->string('nombre_aval')->nullable();
            $table->string('actividad_productiva')->nullable();
            $table->integer('anios_experiencia')->nullable();
            $table->decimal('ingreso_mensual', 10, 2)->default(0);
            $table->decimal('gasto_mensual_familiar', 10, 2)->default(0);
            $table->decimal('credito_solicitado', 10, 2)->default(0);
            $table->string('estado')->nullable();
            $table->string('municipio')->nullable();
            $table->string('colonia')->nullable();
            $table->string('codigo_postal')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
