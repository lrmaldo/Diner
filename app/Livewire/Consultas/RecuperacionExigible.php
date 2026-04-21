<?php

namespace App\Livewire\Consultas;

use App\Models\User;
use App\Services\CalculadoraPrestamos;
use Livewire\Component;
use Livewire\Attributes\Url;

class RecuperacionExigible extends Component
{
    #[Url]
    public $fechaDesde;
    
    #[Url]
    public $fechaHasta;
    
    #[Url]
    public $showReport = false;
    
    #[Url]
    public $sortColumn = 'exigible';
    
    #[Url]
    public $sortDirection = 'desc';

    public function mount()
    {
        $this->fechaDesde = $this->fechaDesde ?? date('Y-m-01');
        $this->fechaHasta = $this->fechaHasta ?? date('Y-m-t');
    }

    public function sortBy($column)
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'desc'; // Por defecto empezamos ordenando de mayor a menor
            $this->sortColumn = $column;
        }
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
            // Obtener los asesores que tienen prÃ©stamos autorizados o pagados o entregados
            $asesores = User::whereHas('prestamosComoAsesor', function ($q) {
            $q->whereIn('estado', ['autorizado', 'entregado', 'pagado', 'liquidado', 'castigado']);
        })->with(['prestamosComoAsesor' => function ($q) {
            $q->whereIn('estado', ['autorizado', 'entregado', 'pagado', 'liquidado', 'castigado'])
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

                    // --- DISTRIBUCIÓN FIFO PARA EL RECUPERADO ---
                    $todosLosPagos = $prestamo->pagos->sortBy([['fecha_pago', 'asc'], ['id', 'asc']])->filter(function($p) {
                        $tipo = strtolower($p->tipo_pago ?? '');
                        return !in_array($tipo, ['garantia', 'garantía', 'seguro', 'cargo']) && !str_contains($tipo, 'devolucion');
                    });

                    $colaPagos = [];
                    foreach($todosLosPagos as $p) {
                        $capitalNeto = (float)$p->monto - (float)$p->moratorio_pagado;
                        $colaPagos[] = [
                            'remanente' => max(0, $capitalNeto)
                        ];
                    }

                    $recuperadoPorCuota = [];
                    foreach($calendario as $c) {
                        $montoRequerido = (float)$c['monto'];
                        $pagadoParaEstaCuota = 0;
                        
                        foreach($colaPagos as &$entry) {
                            if ($entry['remanente'] <= 0.001) continue;
                            
                            $tomar = min($entry['remanente'], $montoRequerido - $pagadoParaEstaCuota);
                            if ($tomar > 0) {
                                $pagadoParaEstaCuota += $tomar;
                                $entry['remanente'] -= $tomar;
                            }
                            if ($pagadoParaEstaCuota >= $montoRequerido - 0.001) {
                                break;
                            }
                        }
                        $recuperadoPorCuota[$c['numero']] = $pagadoParaEstaCuota;
                    }
                    // --- FIN DISTRIBUCIÓN FIFO ---

                    // Sumar el exigible que cae en las fechas indicadas
                    foreach ($calendario as $cuota) {
                        if ($cuota['fecha'] >= $this->fechaDesde && $cuota['fecha'] <= $this->fechaHasta) {
                            $exigibleTotal += $cuota['monto'];

                            $recuperadoCuota = $recuperadoPorCuota[$cuota['numero']] ?? 0;

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

        $collection = collect($asesoresResult);
        if ($this->sortColumn) {
            $collection = $this->sortDirection === 'asc' 
                ? $collection->sortBy($this->sortColumn) 
                : $collection->sortByDesc($this->sortColumn);
        }

        return view('livewire.consultas.recuperacion-exigible', [
            'resultados' => $collection->values(),
        ]);
    }
}
