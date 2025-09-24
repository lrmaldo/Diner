<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('telefonos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->enum('tipo', ['casa', 'celular', 'trabajo', 'otro'])->default('celular');
            $table->string('numero');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telefonos');
    }
};
