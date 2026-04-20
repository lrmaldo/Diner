<?php

namespace App\Livewire\Consultas;

use App\Models\Prestamo;
use App\Models\User;
use App\Services\CalculadoraPrestamos;
use Livewire\Component;

class RecuperacionAsesor extends Component
{
    public $asesor_id;

    public $fechaDesde;

    public $fechaHasta;

    public $sortColumn = '';

    public $sortDirection = 'asc';

    public function mount($asesor_id, $desde, $hasta)
    {
        $this->asesor_id = $asesor_id;
        $this->fechaDesde = $desde;
        $this->fechaHasta = $hasta;
    }

    public function sortBy($column)
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'desc'; // Por defecto los numÃ©ros suelen ordenarse desc
            $this->sortColumn = $column;
        }
    }

    public function generateReport()
    {
        $this->validate([
            'fechaDesde' => 'required|date',
            'fechaHasta' => 'required|date|after_or_equal:fechaDesde',
        ]);

        // Render will refresh
    }

    public function render()
    {
        $asesor = User::findOrFail($this->asesor_id);

        $cuotasResult = [];

        // Obtener los préstamos del asesor con las relaciones requeridas
        $prestamos = Prestamo::where('asesor_id', $this->asesor_id)
            ->whereIn('estado', ['autorizado', 'entregado', 'pagado', 'liquidado', 'castigado'])
            ->with(['cliente', 'representante', 'grupo', 'pagos'])
            ->get();

        foreach ($prestamos as $prestamo) {
            try {
                $calendario = CalculadoraPrestamos::calcularCalendarioPagos(
                    $prestamo->monto_autorizado ?? $prestamo->monto_total,
                    $prestamo->tasa_interes,
                    $prestamo->plazo,
                    $prestamo->periodicidad,
                    $prestamo->fecha_primer_pago ?? $prestamo->fecha_entrega,
                    $prestamo->ultimo_pago ?? null
                );

                foreach ($calendario as $cuota) {
                    // Validar si esta cuota debe pagarse en el rango indicado
                    if ($cuota['fecha'] >= $this->fechaDesde && $cuota['fecha'] <= $this->fechaHasta) {

                        $exigible = $cuota['monto'];

                        // Encontrar cuánto se ha pagado a este numero de pago.
                        // Nota: el PDF dice "sumatoria del monto que el cliente pago".
                        // Si el cliente pagó la cuota 1 en múltiples abonos, se suman los pagos que tengan
                        // numero_pago = $cuota['numero']
                        $recuperado = $prestamo->pagos
                            ->where('numero_pago', $cuota['numero'])
                            ->where('tipo_pago', '!=', 'cargo')
                            ->sum('monto');

                        $pendiente = max(0, $exigible - $recuperado);
                        $eficiencia = $exigible > 0 ? ($recuperado / $exigible) * 100 : 100;

                        // Determinar datos de presentación
                        $grupo = $prestamo->id;

                        $nombreBase = $prestamo->representante
                            ? trim($prestamo->representante->nombres.' '.$prestamo->representante->apellido_paterno.' '.$prestamo->representante->apellido_materno)
                            : ($prestamo->cliente ? trim($prestamo->cliente->nombres.' '.$prestamo->cliente->apellido_paterno.' '.$prestamo->cliente->apellido_materno) : 'Sin representante');
                        
                        $tipoProducto = ucfirst($prestamo->producto ?? 'Individual');
                        $representante = $nombreBase . ' (' . $tipoProducto . ')';

                        $cuotasResult[] = [
                            'prestamo_id' => $prestamo->id,
                            'grupo' => $grupo,
                            'representante' => $representante,
                            'vencimiento' => $cuota['fecha_format'], // d-m-Y
                            'pago' => $cuota['numero'].'/'.$cuota['total_pagos'],
                            'exigible' => $exigible,
                            'recuperado' => $recuperado,
                            'pendiente' => $pendiente,
                            'eficiencia' => min(100, $eficiencia),
                        ];
                    }
                }
            } catch (\Exception $e) {
                continue; // saltar si la config del prestamo esta corrupta
            }
        }

        $collection = collect($cuotasResult);
        if ($this->sortColumn) {
            $collection = $this->sortDirection === 'asc' 
                ? $collection->sortBy($this->sortColumn) 
                : $collection->sortByDesc($this->sortColumn);
        }

        return view('livewire.consultas.recuperacion-asesor', [
            'asesor' => $asesor,
            'resultados' => $collection->values()->all(),
        ]);
    }
}
