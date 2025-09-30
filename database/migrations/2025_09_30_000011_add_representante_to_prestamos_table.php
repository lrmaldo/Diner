<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            if (! Schema::hasColumn('prestamos', 'representante_id')) {
                $table->unsignedBigInteger('representante_id')->nullable()->after('grupo_id');
                // Nota: no agregamos constraint para compatibilidad amplia (SQLite, etc.)
            }
        });
    }

    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            if (Schema::hasColumn('prestamos', 'representante_id')) {
                $table->dropColumn('representante_id');
            }
        });
    }
};
