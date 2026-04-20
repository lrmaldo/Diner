<?php

namespace App\Livewire\Consultas;

use App\Models\User;
use App\Services\CalculadoraPrestamos;
use Livewire\Component;

class RecuperacionExigible extends Component
{
    public $fechaDesde;
    public $fechaHasta;
    public $showReport = false;

    public function mount()
    {
        $this->fechaDesde = date('Y-m-01');
        $this->fechaHasta = date('Y-m-t');
    }

    public function generateReport()
    {
        $this->validate([
            'fechaDesde' => 'required|date',
            'fechaHasta' => 'required|date|after_or_equal:fechaDesde',
        ]);
        
        $this->showReport = true;
    }

    public function render()
    {
        $asesoresResult = [];

        if ($this->showReport) {
            // Obtener los asesores que tienen prÃ©stamos autorizados o pagados
            $asesores = User::whereHas('prestamosComoAsesor', function ($q) {
            $q->whereIn('estado', ['autorizado', 'pagado', 'castigado']);
        })->with(['prestamosComoAsesor' => function ($q) {
            $q->whereIn('estado', ['autorizado', 'pagado', 'castigado'])
                ->with('pagos'); // Cargamos todos los pagos para poder cruzar por numero_pago
        }])->get();

        foreach ($asesores as $asesor) {
            $exigibleTotal = 0;
            $recuperadoTotal = 0;

            foreach ($asesor->prestamosComoAsesor as $prestamo) {
                // Generar calendario de este préstamo
                try {
                    $calendario = CalculadoraPrestamos::calcularCalendarioPagos(
                        $prestamo->monto_autorizado ?? $prestamo->monto_total,
                        $prestamo->tasa_interes,
                        $prestamo->plazo,
                        $prestamo->periodicidad,
                        $prestamo->fecha_primer_pago ?? $prestamo->fecha_entrega,
                        $prestamo->ultimo_pago ?? null
                    );

                    // Sumar el exigible que cae en las fechas indicadas
                    foreach ($calendario as $cuota) {
                        if ($cuota['fecha'] >= $this->fechaDesde && $cuota['fecha'] <= $this->fechaHasta) {
                            $exigibleTotal += $cuota['monto'];

                            // Para mantener concordancia con el detalle del asesor,
                            // Recuperado es la suma de abonos hacia este número de cuota
                            $recuperadoCuota = $prestamo->pagos
                                ->where('numero_pago', $cuota['numero'])
                                ->where('tipo_pago', '!=', 'cargo')
                                ->sum('monto');

                            $recuperadoTotal += $recuperadoCuota;
                        }
                    }
                } catch (\Exception $e) {
                    continue; // saltar si hay error en calculo de calendario
                }
            }

            if ($exigibleTotal > 0 || $recuperadoTotal > 0) {
                $pendiente = max(0, $exigibleTotal - $recuperadoTotal);
                $eficiencia = $exigibleTotal > 0 ? ($recuperadoTotal / $exigibleTotal) * 100 : 100;

                $asesoresResult[] = [
                    'id' => $asesor->id,
                    'nombre' => $asesor->name,
                    'exigible' => $exigibleTotal,
                    'recuperado' => $recuperadoTotal,
                    'pendiente' => $pendiente,
                    'eficiencia' => min(100, $eficiencia), // Cap al 100% como regla visual normal
                ];
            }
        }
        
        } // Closing if ($this->showReport) {

        return view('livewire.consultas.recuperacion-exigible', [
            'resultados' => collect($asesoresResult)->sortByDesc('exigible')->values(),
        ]);
    }
}
