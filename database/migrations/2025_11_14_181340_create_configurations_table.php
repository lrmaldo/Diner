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
        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Clave única para cada configuración
            $table->string('value'); // Valor de la configuración
            $table->string('type')->default('string'); // Tipo de dato: string, decimal, integer, boolean
            $table->string('description')->nullable(); // Descripción de la configuración
            $table->string('category')->default('general'); // Categoría: general, financial, system
            $table->boolean('editable')->default(true); // Si el administrador puede editarlo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurations');
    }
};
