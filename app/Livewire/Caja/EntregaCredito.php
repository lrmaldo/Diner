<?php

namespace App\Livewire\Caja;

use App\Models\Prestamo;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class EntregaCredito extends Component
{
    public $busqueda = '';
    public $prestamo = null;
    public $mostrarDesglose = false;

    // Desglose de efectivo a entregar (Salida de dinero)
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

    public $totalEntregar = 0;
    public $totalSeleccionado = 0;
    public $diferencia = 0;
    public $notas = '';

    public function updatedBusqueda()
    {
        $this->reset(['prestamo', 'mostrarDesglose', 'totalEntregar', 'totalSeleccionado', 'diferencia']);
    }

    public function buscarPrestamo()
    {
        $this->validate([
            'busqueda' => 'required|numeric',
        ]);

        $prestamo = Prestamo::with(['cliente', 'grupo', 'clientes', 'representante', 'asesor'])
            ->find($this->busqueda);

        if (!$prestamo) {
            $this->dispatch('alert', type: 'error', message: 'Préstamo no encontrado.');
            return;
        }

        // Validar si ya fue entregado específicamente
        if ($prestamo->estado === 'entregado') {
            $fecha = $prestamo->fecha_entrega ? $prestamo->fecha_entrega->format('d/m/Y') : 'recientemente';
            $this->dispatch('alert', type: 'warning', message: "Este préstamo YA FUE ENTREGADO el {$fecha}. Estado actual: Entregado");
            return;
        }

        // Validar otros estados no permitidos
        if (!in_array($prestamo->estado, ['aprobado', 'autorizado'])) {
            $estadoActual = ucfirst($prestamo->estado);
            $this->dispatch('alert', type: 'warning', message: "Este préstamo no se puede entregar. Estado actual: {$estadoActual}");
            return;
        }

        $this->prestamo = $prestamo;
        $this->totalEntregar = (float) $prestamo->monto_total; // O monto_autorizado según tu lógica
        
        // Inicializar desglose en ceros
        foreach ($this->desgloseBilletes as $k => $v) $this->desgloseBilletes[$k] = 0;
        foreach ($this->desgloseMonedas as $k => $v) $this->desgloseMonedas[$k] = 0;
        
        $this->calcularTotalSeleccionado();
        
        $this->mostrarDesglose = true;
    }

    public function sugerirDesglose($monto)
    {
        // Resetear
        foreach ($this->desgloseBilletes as $k => $v) $this->desgloseBilletes[$k] = 0;
        foreach ($this->desgloseMonedas as $k => $v) $this->desgloseMonedas[$k] = 0;

        $resto = $monto;
        $denominaciones = [1000, 500, 200, 100, 50, 20, 10, 5, 2, 1, 0.5];

        foreach ($denominaciones as $valor) {
            if (round($resto, 2) >= $valor) {
                $cantidad = floor(round($resto, 2) / $valor);
                $strValor = (string)$valor;

                if ($valor >= 20) {
                    if (isset($this->desgloseBilletes[$strValor])) {
                        $this->desgloseBilletes[$strValor] = $cantidad;
                    } elseif (isset($this->desgloseMonedas[$strValor])) {
                        // Caso especial moneda de 20
                        $this->desgloseMonedas[$strValor] = $cantidad;
                    }
                } else {
                    if (isset($this->desgloseMonedas[$strValor])) {
                        $this->desgloseMonedas[$strValor] = $cantidad;
                    }
                }
                
                $resto = round($resto - ($cantidad * $valor), 2);
            }
        }
    }

    public function updatedDesgloseBilletes()
    {
        $this->calcularTotalSeleccionado();
    }

    public function updatedDesgloseMonedas()
    {
        $this->calcularTotalSeleccionado();
    }

    protected function calcularTotalSeleccionado()
    {
        $this->totalSeleccionado = 0;
        foreach ($this->desgloseBilletes as $denominacion => $cantidad) {
            $this->totalSeleccionado += (float) $denominacion * (int) $cantidad;
        }
        foreach ($this->desgloseMonedas as $denominacion => $cantidad) {
            $this->totalSeleccionado += (float) $denominacion * (int) $cantidad;
        }
        
        $this->diferencia = round($this->totalSeleccionado - $this->totalEntregar, 2);
    }

    public function confirmarEntrega()
    {
        if (!$this->prestamo) return;

        $this->calcularTotalSeleccionado();

        if (abs($this->diferencia) > 0.01) {
            $this->dispatch('alert', type: 'error', message: 'El desglose de efectivo no coincide con el monto a entregar.');
            return;
        }

        try {
            DB::beginTransaction();

            // 1. Actualizar estado del préstamo
            $this->prestamo->estado = 'entregado';
            $this->prestamo->fecha_entrega = now(); // Asumiendo que tienes este campo, si no, usa fecha_inicio
            // Si la fecha de inicio era tentativa, quizás quieras actualizarla a hoy:
            // $this->prestamo->fecha_inicio = now(); 
            $this->prestamo->save();

            // 2. Registrar en Bitácora
            $desglose = [
                'billetes' => array_filter($this->desgloseBilletes, fn ($c) => $c > 0),
                'monedas' => array_filter($this->desgloseMonedas, fn ($c) => $c > 0),
            ];

            $this->prestamo->registrarBitacora(
                'entrega_efectivo',
                "Crédito entregado. Monto: $" . number_format($this->totalEntregar, 2) . ". Entregado por: " . auth()->user()->name,
                ['desglose_salida' => $desglose, 'notas' => $this->notas]
            );

            // Aquí podrías registrar también en una tabla de 'movimientos_caja' si existiera

            DB::commit();

            $this->dispatch('alert', type: 'success', message: 'Crédito entregado exitosamente.');
            $this->reset(['busqueda', 'prestamo', 'mostrarDesglose']);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', type: 'error', message: 'Error al entregar crédito: ' . $e->getMessage());
        }
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.caja.entrega-credito');
    }
}
