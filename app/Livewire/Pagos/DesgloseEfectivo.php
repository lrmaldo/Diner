<?php

namespace App\Livewire\Pagos;

use App\Models\Pago;
use App\Models\Prestamo;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class DesgloseEfectivo extends Component
{
    public $prestamoId;
    public $prestamo;
    public $fechaPago;

    // Array de clientes seleccionados para cobrar [cliente_id => true/false]
    public $clientesSeleccionados = [];

    // Array de montos a pagar por cliente [cliente_id => monto]
    public $montosPorCliente = [];

    // Array de montos de moratorio [cliente_id => moratorio]
    public $moratoriosPorCliente = [];

    // Desglose de efectivo (billetes y monedas)
    public $desgloseBilletes = [
        '1000' => 0,
        '500' => 0,
        '200' => 0,
        '100' => 0,
        '50' => 0,
        '20' => 0,
    ];

    public $desgloseMonedas = [
        '20' => 0,
        '10' => 0,
        '5' => 0,
        '2' => 0,
        '1' => 0,
        '0.5' => 0,
    ];

    // Desglose de cambio (billetes y monedas)
    public $desgloseCambioBilletes = [
        '1000' => 0,
        '500' => 0,
        '200' => 0,
        '100' => 0,
        '50' => 0,
        '20' => 0,
    ];

    public $desgloseCambioMonedas = [
        '20' => 0,
        '10' => 0,
        '5' => 0,
        '2' => 0,
        '1' => 0,
        '0.5' => 0,
    ];

    public $totalEfectivo = 0;
    public $totalSeleccionado = 0;
    public $totalCambioManual = 0;
    public $diferencia = 0;
    public $notas = '';
    public $seleccionarTodos = true; // Por defecto seleccionamos todos
    public $showModalCambio = false;

    public function mount($prestamoId)
    {
        $this->prestamoId = $prestamoId;
        $this->fechaPago = now()->format('Y-m-d');
        $this->loadPrestamo();
    }

    public function loadPrestamo()
    {
        $this->prestamo = Prestamo::with(['clientes', 'representante'])
            ->findOrFail($this->prestamoId);

        if ($this->prestamo->producto === 'grupal' && $this->prestamo->grupo_id) {
            $this->prestamo->load('grupo');
        }

        if ($this->prestamo->producto === 'individual' && $this->prestamo->cliente_id) {
            if ($this->prestamo->clientes->isEmpty()) {
                $this->prestamo->clientes()->syncWithoutDetaching([
                    $this->prestamo->cliente_id => [
                        'monto_solicitado' => $this->prestamo->monto_total,
                        'monto_autorizado' => $this->prestamo->monto_total,
                    ],
                ]);
                $this->prestamo->load('clientes');
            }
        }

        // Intentar recuperar datos de caché enviados desde la vista anterior
        $cacheKey = 'cobro_data_' . auth()->id() . '_' . $this->prestamo->id;
        $cachedData = \Illuminate\Support\Facades\Cache::get($cacheKey);
        // Opcional: Limpiar caché si se desea que sea de un solo uso, pero dejarlo permite recargar la página sin perder datos
        // \Illuminate\Support\Facades\Cache::forget($cacheKey);

        foreach ($this->prestamo->clientes as $cliente) {
            $montoAutorizado = $cliente->pivot->monto_autorizado ?? 0;
            
            // Determinar monto: Prioridad a lo que viene de la vista anterior (caché), sino calcular sugerido
            if ($cachedData && isset($cachedData['abonos'][$cliente->id])) {
                $montoSugerido = (float) $cachedData['abonos'][$cliente->id];
            } else {
                $montoSugerido = $this->calcularMontoSugerido($montoAutorizado);
            }

            $this->montosPorCliente[$cliente->id] = $montoSugerido;
            $this->moratoriosPorCliente[$cliente->id] = 0;
            
            // Determinar selección: Prioridad a caché, sino true por defecto
            if ($cachedData && isset($cachedData['selectedClients'][$cliente->id])) {
                $this->clientesSeleccionados[$cliente->id] = $cachedData['selectedClients'][$cliente->id];
            } else {
                $this->clientesSeleccionados[$cliente->id] = true; // Default selected
            }
        }

        $this->calcularTotales();
    }

    protected function calcularMontoSugerido(float $montoAutorizado): float
    {
        if ($montoAutorizado <= 0) {
            return 0;
        }

        $plazo = strtolower(trim($this->prestamo->plazo));
        $periodicidad = strtolower(trim($this->prestamo->periodicidad));
        $tasaInteres = (float) $this->prestamo->tasa_interes;

        $configuracion = $this->determinarConfiguracionPago($plazo, $periodicidad);

        if (! $configuracion) {
            $interesTotal = $montoAutorizado * ($tasaInteres / 100);
            $montoTotal = $montoAutorizado + $interesTotal;
            return round($montoTotal / $this->extraerPlazoNumerico($plazo), 2);
        }

        $interes = (($montoAutorizado / 100) * $tasaInteres) * $configuracion['meses_interes'];
        $ivaPorcentaje = \App\Models\Configuration::get('iva_percentage', 16);
        $iva = ($interes / 100) * $ivaPorcentaje;
        $montoTotal = $interes + $iva + $montoAutorizado;

        $pagoConDecimales = $montoTotal / $configuracion['total_pagos'];
        return floor($pagoConDecimales);
    }

    protected function extraerPlazoNumerico(string $plazo): int
    {
        if (is_numeric($plazo)) {
            return (int) $plazo;
        }
        preg_match('/(\d+)/', $plazo, $matches);
        return isset($matches[1]) ? (int) $matches[1] : 1;
    }

    protected function determinarConfiguracionPago(string $plazo, string $periodicidad): ?array
    {
        // Reutilizamos la lógica de configuración (simplificada aquí por brevedad, pero debería ser idéntica)
        // En un refactor real, esto debería estar en un Service o Trait.
        // Copio el array completo para asegurar consistencia.
        $configuraciones = [
            '4 meses_semanal' => ['meses_interes' => 4, 'total_pagos' => 16],
            '4meses_semanal' => ['meses_interes' => 4, 'total_pagos' => 16],
            '4 meses_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 8],
            '4meses_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 8],
            '4 meses_quincenal' => ['meses_interes' => 4, 'total_pagos' => 8],
            '4meses_quincenal' => ['meses_interes' => 4, 'total_pagos' => 8],
            '4 meses d_semanal' => ['meses_interes' => 4, 'total_pagos' => 14],
            '4meses d_semanal' => ['meses_interes' => 4, 'total_pagos' => 14],
            '4mesesd_semanal' => ['meses_interes' => 4, 'total_pagos' => 14],
            '4 meses d_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4meses d_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4mesesd_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4 meses d_quincenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4meses d_quincenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4mesesd_quincenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '5 meses d_semanal' => ['meses_interes' => 5, 'total_pagos' => 18],
            '5meses d_semanal' => ['meses_interes' => 5, 'total_pagos' => 18],
            '5mesesd_semanal' => ['meses_interes' => 5, 'total_pagos' => 18],
            '5 meses d_catorcenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5meses d_catorcenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5mesesd_catorcenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5 meses d_quincenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5meses d_quincenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5mesesd_quincenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '6 meses_semanal' => ['meses_interes' => 6, 'total_pagos' => 24],
            '6meses_semanal' => ['meses_interes' => 6, 'total_pagos' => 24],
            '6 meses_catorcenal' => ['meses_interes' => 6, 'total_pagos' => 12],
            '6meses_catorcenal' => ['meses_interes' => 6, 'total_pagos' => 12],
            '6 meses_quincenal' => ['meses_interes' => 6, 'total_pagos' => 12],
            '6meses_quincenal' => ['meses_interes' => 6, 'total_pagos' => 12],
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

    public function updatedSeleccionarTodos($value)
    {
        foreach ($this->prestamo->clientes as $cliente) {
            $this->clientesSeleccionados[$cliente->id] = $value;
        }
        $this->calcularTotales();
    }

    public function updatedClientesSeleccionados()
    {
        $this->calcularTotales();
    }

    public function updatedMontosPorCliente()
    {
        $this->calcularTotales();
    }

    public function updatedMoratoriosPorCliente()
    {
        $this->calcularTotales();
    }

    public function updatedDesgloseBilletes()
    {
        $this->calcularTotalEfectivo();
    }

    public function updatedDesgloseMonedas()
    {
        $this->calcularTotalEfectivo();
    }

    public function updatedDesgloseCambioBilletes()
    {
        $this->calcularTotalCambioManual();
    }

    public function updatedDesgloseCambioMonedas()
    {
        $this->calcularTotalCambioManual();
    }

    protected function calcularTotalCambioManual()
    {
        $this->totalCambioManual = 0;
        foreach ($this->desgloseCambioBilletes as $denominacion => $cantidad) {
            $this->totalCambioManual += (float) $denominacion * (int) $cantidad;
        }
        foreach ($this->desgloseCambioMonedas as $denominacion => $cantidad) {
            $this->totalCambioManual += (float) $denominacion * (int) $cantidad;
        }
    }

    protected function calcularTotales()
    {
        $this->totalSeleccionado = 0;
        foreach ($this->clientesSeleccionados as $clienteId => $seleccionado) {
            if ($seleccionado) {
                $monto = (float) ($this->montosPorCliente[$clienteId] ?? 0);
                $moratorio = (float) ($this->moratoriosPorCliente[$clienteId] ?? 0);
                $this->totalSeleccionado += $monto + $moratorio;
            }
        }
        $this->calcularDiferencia();
    }

    protected function calcularTotalEfectivo()
    {
        $this->totalEfectivo = 0;
        foreach ($this->desgloseBilletes as $denominacion => $cantidad) {
            $this->totalEfectivo += (float) $denominacion * (int) $cantidad;
        }
        foreach ($this->desgloseMonedas as $denominacion => $cantidad) {
            $this->totalEfectivo += (float) $denominacion * (int) $cantidad;
        }
        $this->calcularDiferencia();
    }

    protected function calcularDiferencia()
    {
        $this->diferencia = round($this->totalEfectivo - $this->totalSeleccionado, 2);
    }

    public function getDesgloseCambioProperty()
    {
        if ($this->diferencia <= 0) {
            return [];
        }

        $cambio = $this->diferencia;
        // Denominaciones disponibles en México
        $denominaciones = [1000, 500, 200, 100, 50, 20, 10, 5, 2, 1, 0.5];
        $resultado = [];

        foreach ($denominaciones as $valor) {
            // Usamos round para evitar problemas de precisión flotante
            if (round($cambio, 2) >= $valor) {
                $cantidad = floor(round($cambio, 2) / $valor);
                if ($cantidad > 0) {
                    $resultado[(string)$valor] = $cantidad;
                    $cambio = round($cambio - ($cantidad * $valor), 2);
                }
            }
        }

        return $resultado;
    }

    public function validarRegistro()
    {
        // Asegurar que los totales estén actualizados antes de validar
        $this->calcularTotales();
        $this->calcularTotalEfectivo();

        $clientesSeleccionadosCount = count(array_filter($this->clientesSeleccionados));

        if ($clientesSeleccionadosCount === 0) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Debe seleccionar al menos un cliente.']);
            return;
        }

        if ($this->totalEfectivo <= 0) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Debe ingresar el efectivo recibido.']);
            return;
        }

        if ($this->diferencia < 0) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'El efectivo recibido es insuficiente.']);
            return;
        }

        // Resetear desglose de cambio
        foreach ($this->desgloseCambioBilletes as $k => $v) $this->desgloseCambioBilletes[$k] = 0;
        foreach ($this->desgloseCambioMonedas as $k => $v) $this->desgloseCambioMonedas[$k] = 0;

        // Pre-llenar con la sugerencia
        $sugerencia = $this->desgloseCambio;
        
        foreach ($sugerencia as $denominacion => $cantidad) {
            $strDenominacion = (string)$denominacion;
            if (isset($this->desgloseCambioBilletes[$strDenominacion])) {
                $this->desgloseCambioBilletes[$strDenominacion] = $cantidad;
            } elseif (isset($this->desgloseCambioMonedas[$strDenominacion])) {
                $this->desgloseCambioMonedas[$strDenominacion] = $cantidad;
            }
        }
        
        $this->calcularTotalCambioManual();

        // Si todo es correcto, mostramos el modal de confirmación/cambio
        $this->showModalCambio = true;
    }

    public function finalizarRegistro()
    {
        try {
            DB::beginTransaction();

            $desglose = [
                'billetes' => array_filter($this->desgloseBilletes, fn ($cantidad) => $cantidad > 0),
                'monedas' => array_filter($this->desgloseMonedas, fn ($cantidad) => $cantidad > 0),
            ];

            foreach ($this->clientesSeleccionados as $clienteId => $seleccionado) {
                if ($seleccionado) {
                    $monto = (float) ($this->montosPorCliente[$clienteId] ?? 0);
                    $moratorio = (float) ($this->moratoriosPorCliente[$clienteId] ?? 0);
                    $montoTotal = $monto + $moratorio;

                    if ($montoTotal > 0) {
                        $ultimoPago = Pago::where('prestamo_id', $this->prestamo->id)
                            ->where('cliente_id', $clienteId)
                            ->whereNotNull('numero_pago')
                            ->max('numero_pago');

                        $numeroPago = $ultimoPago ? $ultimoPago + 1 : 1;

                        Pago::create([
                            'prestamo_id' => $this->prestamo->id,
                            'cliente_id' => $clienteId,
                            'registrado_por' => auth()->id(),
                            'monto' => $montoTotal,
                            'fecha_pago' => $this->fechaPago,
                            'tipo_pago' => 'abono',
                            'numero_pago' => $numeroPago,
                            'interes_pagado' => 0,
                            'capital_pagado' => $monto,
                            'moratorio_pagado' => $moratorio,
                            'notas' => $this->notas,
                            'desglose_efectivo' => $desglose,
                        ]);
                    }
                }
            }

            $clientesSeleccionadosCount = count(array_filter($this->clientesSeleccionados));
            $tipoPrestamo = $this->prestamo->producto === 'grupal' ? 'Cobro grupal' : 'Pago';
            $detalleClientes = $clientesSeleccionadosCount > 1 ? "{$clientesSeleccionadosCount} clientes" : '1 cliente';

            $this->prestamo->registrarBitacora(
                'pago_registrado',
                "{$tipoPrestamo} registrado: {$detalleClientes}, total: $".number_format($this->totalSeleccionado, 2)
            );

            DB::commit();

            $this->dispatch('alert', ['type' => 'success', 'message' => 'Pagos registrados exitosamente.']);
            return redirect()->route('prestamos.show', $this->prestamo->id);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Error: '.$e->getMessage()]);
        }
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.pagos.desglose-efectivo', [
            'grupo' => $this->prestamo->grupo ?? null,
            'representante' => $this->prestamo->representante ?? $this->prestamo->cliente,
            'clientes' => $this->prestamo->clientes,
        ]);
    }
}
