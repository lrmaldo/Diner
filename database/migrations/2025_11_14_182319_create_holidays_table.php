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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del día feriado
            $table->date('date'); // Fecha del día feriado
            $table->year('year'); // Año del feriado
            $table->boolean('is_recurring')->default(false); // Si se repite cada año (ej: Día de Independencia)
            $table->string('type')->default('national'); // Tipo: national, local, religious, etc.
            $table->text('description')->nullable(); // Descripción opcional
            $table->boolean('is_active')->default(true); // Si está activo este año
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['date', 'is_active']);
            $table->index('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
