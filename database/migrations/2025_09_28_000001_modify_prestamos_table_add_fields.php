<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            // cliente_id para préstamos individuales
            if (! Schema::hasColumn('prestamos', 'cliente_id')) {
                $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            }

            if (! Schema::hasColumn('prestamos', 'folio')) {
                $table->string('folio')->unique()->nullable()->after('id');
            }

            // producto: individual o grupal
            if (! Schema::hasColumn('prestamos', 'producto')) {
                $table->string('producto')->nullable()->after('folio');
            }

            // plazo values: 4meses, 4mesesD, 5mesesD, 6meses, 1ano
            if (! Schema::hasColumn('prestamos', 'plazo')) {
                $table->string('plazo')->nullable()->after('producto');
            }

            if (! Schema::hasColumn('prestamos', 'periodicidad')) {
                $table->string('periodicidad')->nullable()->after('plazo');
            }

            if (! Schema::hasColumn('prestamos', 'fecha_primer_pago')) {
                $table->date('fecha_primer_pago')->nullable()->after('fecha_entrega');
            }

            if (! Schema::hasColumn('prestamos', 'monto_total')) {
                $table->decimal('monto_total', 12, 2)->nullable()->after('tasa_interes');
            }

            // estado
            if (! Schema::hasColumn('prestamos', 'estado')) {
                $table->string('estado')->default('en_curso')->after('monto_total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            if (Schema::hasColumn('prestamos', 'cliente_id')) {
                $table->dropConstrainedForeignId('cliente_id');
            }
            $table->dropColumn(['folio', 'producto', 'plazo', 'periodicidad', 'fecha_primer_pago', 'monto_total', 'estado']);
        });
    }
};
