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
        Schema::create('capitalizaciones', function (Blueprint $table) {
            $table->id();
            $table->decimal('monto', 12, 2);
            $table->json('desglose_billetes')->nullable(); // Guardará el conteo de billetes: {"1000": 0, "500": 20...}
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Usuario que registró
            $table->string('comentarios')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capitalizaciones');
    }
};
