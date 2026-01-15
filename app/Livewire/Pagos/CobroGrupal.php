<?php

namespace App\Livewire\Pagos;

use App\Models\Pago;
use App\Models\Prestamo;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CobroGrupal extends Component
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
        '0_5' => 0,
    ];

    public $totalEfectivo = 0;

    public $totalSeleccionado = 0;

    public $diferencia = 0;

    public $notas = '';

    public $mostrarContadorEfectivo = false;

    public $seleccionarTodos = false;

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

        // Cargar el grupo si es préstamo grupal
        if ($this->prestamo->producto === 'grupal' && $this->prestamo->grupo_id) {
            $this->prestamo->load('grupo');
        }

        // Para préstamos individuales, asegurar que el cliente esté en la relación clientes
        if ($this->prestamo->producto === 'individual' && $this->prestamo->cliente_id) {
            // Si no hay clientes en la relación pivot, sincronizarlos
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

        // Inicializar montos sugeridos para cada cliente
        foreach ($this->prestamo->clientes as $cliente) {
            $montoAutorizado = $cliente->pivot->monto_autorizado ?? 0;

            // Calcular el monto sugerido basado en periodicidad
            $montoSugerido = $this->calcularMontoSugerido($montoAutorizado);

            $this->montosPorCliente[$cliente->id] = $montoSugerido;
            $this->moratoriosPorCliente[$cliente->id] = 0;
        }

        $this->calcularTotales();
    }

    /**
     * Calcular el monto sugerido basado en el monto autorizado y periodicidad
     */
    protected function calcularMontoSugerido(float $montoAutorizado): float
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

            return round($montoTotal / $this->extraerPlazoNumerico($plazo), 2);
        }

        // Calcular según reglas de negocio específicas
        $interes = (($montoAutorizado / 100) * $tasaInteres) * $configuracion['meses_interes'];
        $ivaPorcentaje = \App\Models\Configuration::get('iva_percentage', 16);
        $iva = ($interes / 100) * $ivaPorcentaje;
        $montoTotal = $interes + $iva + $montoAutorizado;

        // Calcular pago regular
        $pagoConDecimales = $montoTotal / $configuracion['total_pagos'];
        $pagoRegular = floor($pagoConDecimales);

        return $pagoRegular;
    }

    /**
     * Extraer número de plazo (e.g., "4meses" => 4)
     */
    protected function extraerPlazoNumerico(string $plazo): int
    {
        if (is_numeric($plazo)) {
            return (int) $plazo;
        }
        preg_match('/(\d+)/', $plazo, $matches);

        return isset($matches[1]) ? (int) $matches[1] : 1;
    }

    /**
     * Determinar configuración de pago según plazo y periodicidad
     */
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
            $val = $denominacion === '0_5' ? 0.5 : (float) $denominacion;
            $this->totalEfectivo += $val * (int) $cantidad;
        }

        $this->calcularDiferencia();
    }

    protected function calcularDiferencia()
    {
        $this->diferencia = $this->totalEfectivo - $this->totalSeleccionado;
    }

    public function toggleContadorEfectivo()
    {
        $this->mostrarContadorEfectivo = ! $this->mostrarContadorEfectivo;
    }

    public function registrarPagos()
    {
        // Validación
        $clientesSeleccionadosCount = count(array_filter($this->clientesSeleccionados));

        if ($clientesSeleccionadosCount === 0) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Debe seleccionar al menos un cliente para registrar el pago.',
            ]);

            return;
        }

        if ($this->totalEfectivo <= 0) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Debe ingresar el efectivo recibido.',
            ]);

            return;
        }

        if ($this->diferencia < 0) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'El efectivo recibido es insuficiente. Faltan: $'.number_format(abs($this->diferencia), 2),
            ]);

            return;
        }

        try {
            DB::beginTransaction();

            $desglose = [
                'billetes' => array_filter($this->desgloseBilletes, fn ($cantidad) => $cantidad > 0),
                'monedas' => array_filter($this->desgloseMonedas, fn ($cantidad) => $cantidad > 0),
            ];

            // Crear pagos para cada cliente seleccionado
            foreach ($this->clientesSeleccionados as $clienteId => $seleccionado) {
                if ($seleccionado) {
                    $monto = (float) ($this->montosPorCliente[$clienteId] ?? 0);
                    $moratorio = (float) ($this->moratoriosPorCliente[$clienteId] ?? 0);
                    $montoTotal = $monto + $moratorio;

                    if ($montoTotal > 0) {
                        // Calcular el número de pago (siguiente pago después del último registrado)
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
                            'interes_pagado' => 0, // Calcular según lógica de negocio
                            'capital_pagado' => $monto,
                            'moratorio_pagado' => $moratorio,
                            'notas' => $this->notas,
                            'desglose_efectivo' => $desglose,
                        ]);
                    }
                }
            }

            // Registrar en bitácora
            $tipoPrestamo = $this->prestamo->producto === 'grupal' ? 'Cobro grupal' : 'Pago';
            $detalleClientes = $clientesSeleccionadosCount > 1 ? "{$clientesSeleccionadosCount} clientes" : '1 cliente';

            $this->prestamo->registrarBitacora(
                'pago_registrado',
                "{$tipoPrestamo} registrado: {$detalleClientes}, total: $".number_format($this->totalSeleccionado, 2)
            );

            DB::commit();

            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'Pagos registrados exitosamente.',
            ]);

            // Redirigir a la lista de pagos/busqueda
            return redirect()->route('pagos.index');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Error al registrar los pagos: '.$e->getMessage(),
            ]);
        }
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.pagos.cobro-grupal', [
            'grupo' => $this->prestamo->grupo ?? null,
            'representante' => $this->prestamo->representante ?? $this->prestamo->cliente,
            'clientes' => $this->prestamo->clientes,
        ]);
    }
}
