<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$asesores = App\Models\User::whereHas('prestamosComoAsesor', function ($q) {
    $q->whereIn('estado', ['autorizado', 'pagado', 'castigado']);
})->get();

echo "Asesores encontrados: " . $asesores->count() . "\n";

foreach ($asesores as $asesor) {
    echo "Asesor: {$asesor->name}\n";
    $prestamos = $asesor->prestamosComoAsesor()->whereIn('estado', ['autorizado', 'pagado', 'castigado'])->get();
    echo "  Prestamos: " . $prestamos->count() . "\n";
    
    foreach ($prestamos as $prestamo) {
        try {
            $calendario = App\Services\CalculadoraPrestamos::calcularCalendarioPagos(
                $prestamo->monto_autorizado ?? $prestamo->monto_total,
                $prestamo->tasa_interes,
                $prestamo->plazo,
                $prestamo->periodicidad,
                $prestamo->fecha_primer_pago ?? $prestamo->fecha_entrega,
                $prestamo->ultimo_pago ?? null
            );
            
            $fechas = array_column($calendario, 'fecha');
            echo "      Prestamo #{$prestamo->id}: " . count($calendario) . " cuotas. Fechas entre " . min($fechas) . " y " . max($fechas) . "\n";
        } catch (\Exception $e) {
            echo "      Error en Prestamo #{$prestamo->id}: " . $e->getMessage() . "\n";
        }
    }
}
