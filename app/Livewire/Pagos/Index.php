<?php

namespace App\Livewire\Pagos;

use App\Models\Prestamo;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    public $search = '';

    public $prestamo = null;

    public $notFound = false;

    public function updatedSearch()
    {
        $this->buscarPrestamo();
    }

    public function buscarPrestamo()
    {
        $this->notFound = false;
        $this->prestamo = null;

        if (empty($this->search)) {
            return;
        }

        // Buscar por ID de préstamo
        $this->prestamo = Prestamo::with(['cliente', 'representante', 'asesor', 'grupo', 'clientes', 'pagos'])
            ->find($this->search);

        if (! $this->prestamo) {
            $this->notFound = true;
        }
    }

    public function calcularCuota($montoAutorizado)
    {
        if ($montoAutorizado <= 0) {
            return 0;
        }

        $plazo = strtolower(trim($this->prestamo->plazo));
        $periodicidad = strtolower(trim($this->prestamo->periodicidad));
        $tasaInteres = (float) $this->prestamo->tasa_interes;

        // Determinar configuración según reglas de negocio
        $configuracion = $this->determinarConfiguracionPago($plazo, $periodicidad);

        if (! $configuracion) {
            // Fallback: cálculo básico
            $interesTotal = $montoAutorizado * ($tasaInteres / 100);
            $montoTotal = $montoAutorizado + $interesTotal;
            $plazoNum = preg_match('/(\d+)/', $plazo, $matches) ? (int) $matches[1] : 1;

            return round($montoTotal / $plazoNum, 2);
        }

        // Calcular según reglas de negocio específicas
        $interes = (($montoAutorizado / 100) * $tasaInteres) * $configuracion['meses_interes'];
        $ivaPorcentaje = \App\Models\Configuration::get('iva_percentage', 16);
        $iva = ($interes / 100) * $ivaPorcentaje;
        $montoTotal = $interes + $iva + $montoAutorizado;

        // Calcular pago regular
        $pagoConDecimales = $montoTotal / $configuracion['total_pagos'];

        return floor($pagoConDecimales);
    }

    protected function determinarConfiguracionPago(string $plazo, string $periodicidad): ?array
    {
        $configuraciones = [
            // Caso 1: 4 meses
            '4 meses_semanal' => ['meses_interes' => 4, 'total_pagos' => 16],
            '4meses_semanal' => ['meses_interes' => 4, 'total_pagos' => 16],
            '4 meses_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 8],
            '4meses_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 8],
            '4 meses_quincenal' => ['meses_interes' => 4, 'total_pagos' => 8],
            '4meses_quincenal' => ['meses_interes' => 4, 'total_pagos' => 8],

            // Caso 2: 4 meses D
            '4 meses d_semanal' => ['meses_interes' => 4, 'total_pagos' => 14],
            '4meses d_semanal' => ['meses_interes' => 4, 'total_pagos' => 14],
            '4mesesd_semanal' => ['meses_interes' => 4, 'total_pagos' => 14],
            '4 meses d_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4meses d_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4mesesd_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4 meses d_quincenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4meses d_quincenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4mesesd_quincenal' => ['meses_interes' => 4, 'total_pagos' => 7],

            // Caso 3: 5 meses D
            '5 meses d_semanal' => ['meses_interes' => 5, 'total_pagos' => 18],
            '5meses d_semanal' => ['meses_interes' => 5, 'total_pagos' => 18],
            '5mesesd_semanal' => ['meses_interes' => 5, 'total_pagos' => 18],
            '5 meses d_catorcenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5meses d_catorcenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5mesesd_catorcenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5 meses d_quincenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5meses d_quincenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5mesesd_quincenal' => ['meses_interes' => 5, 'total_pagos' => 9],

            // Caso 4: 6 meses
            '6 meses_semanal' => ['meses_interes' => 6, 'total_pagos' => 24],
            '6meses_semanal' => ['meses_interes' => 6, 'total_pagos' => 24],
            '6 meses_catorcenal' => ['meses_interes' => 6, 'total_pagos' => 12],
            '6meses_catorcenal' => ['meses_interes' => 6, 'total_pagos' => 12],
            '6 meses_quincenal' => ['meses_interes' => 6, 'total_pagos' => 12],
            '6meses_quincenal' => ['meses_interes' => 6, 'total_pagos' => 12],

            // Caso 5: 1 año
            '1 año_semanal' => ['meses_interes' => 12, 'total_pagos' => 48],
            '1año_semanal' => ['meses_interes' => 12, 'total_pagos' => 48],
            '1 ano_semanal' => ['meses_interes' => 12, 'total_pagos' => 48],
            '1ano_semanal' => ['meses_interes' => 12, 'total_pagos' => 48],
            '1 año_catorcenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1año_catorcenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1 ano_catorcenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1ano_catorcenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1 año_quincenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1año_quincenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1 ano_quincenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1ano_quincenal' => ['meses_interes' => 12, 'total_pagos' => 24],
        ];

        $clave = $plazo.'_'.$periodicidad;

        return $configuraciones[$clave] ?? null;
    }

    public function irACobrar()
    {
        if ($this->prestamo) {
            return redirect()->route('pagos.desglose-efectivo', $this->prestamo->id);
        }
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.pagos.index');
    }
}
