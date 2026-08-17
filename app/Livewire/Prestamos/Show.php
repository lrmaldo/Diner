<?php

namespace App\Livewire\Prestamos;

use App\Models\Cliente;
use App\Models\Prestamo;
use App\Services\CalculadoraPrestamos;
use Carbon\Carbon;
use Livewire\Component;

class Show extends Component
{
    public $prestamoId;

    public $prestamo;

    public $comentarios = '';

    public $motivoRechazo = '';

    // Array para almacenar montos autorizados temporalmente
    public $montosAutorizados = [];

    // Simplificación temporal: desactivar historial y estadísticas
    // Mantener propiedades comentadas para evitar referencias en vistas futuras
    // public $historialPrestamos;
    // public $porcentajeCumplimiento = 0;
    // public $totalHistorial = 0;
    // public $estadisticas = [
    //     'total_prestado' => 0,
    //     'total_autorizado' => 0,
    //     'total_rechazado' => 0,
    //     'promedio_monto' => 0,
    //     'prestamos_activos' => 0,
    // ];

    public function mount($id)
    {
        $this->prestamoId = $id;
        $this->loadPrestamo();
    }

    public function loadPrestamo()
    {
        $this->prestamo = Prestamo::with(['clientes', 'cliente', 'representante'])
            ->findOrFail($this->prestamoId);

        // Cargar montos autorizados de la tabla pivot para préstamos grupales
        if ($this->prestamo->producto === 'grupal' && $this->prestamo->clientes) {
            foreach ($this->prestamo->clientes as $cliente) {
                // Por defecto usar null si no hay monto_autorizado aún
                $this->montosAutorizados[$cliente->id] = $cliente->pivot->monto_autorizado ?? null;
            }
        }
    }

    // Historial temporalmente deshabilitado para evitar sobrecarga
    // public function loadHistorial()
    // {
    // }

    /**
     * Arma las barras del historial crediticio de un cliente para el gráfico de comité.
     * Una barra por cada crédito entregado/finalizado del cliente.
     *
     * @return array<int, array<string, mixed>>
     */
    public function barrasHistorial($cliente): array
    {
        if (! $cliente) {
            return [];
        }

        // Sólo créditos que realmente se entregaron (excluye pendientes/en comité/rechazados).
        // Se incluye el propio crédito para que un cliente con un único crédito no quede sin gráfico.
        $creditos = $cliente->prestamosAsignados()
            ->whereIn('estado', ['entregado', 'atrasado', 'liquidado', 'pagado', 'castigado'])
            ->with('pagos')
            ->orderBy('fecha_entrega')
            ->get();

        // Altura relativa al crédito más grande del historial
        $maxMonto = 0.0;
        foreach ($creditos as $c) {
            $maxMonto = max($maxMonto, (float) ($c->pivot->monto_autorizado ?? $c->monto_total ?? 0));
        }

        $barras = [];
        foreach ($creditos as $c) {
            $monto = (float) ($c->pivot->monto_autorizado ?? $c->monto_total ?? 0);

            try {
                $calendario = CalculadoraPrestamos::calcularCalendarioPagos(
                    $monto,
                    $c->tasa_interes,
                    $c->plazo,
                    $c->periodicidad,
                    $c->fecha_primer_pago ?? $c->fecha_entrega,
                    $c->ultimo_pago ?? null
                );
            } catch (\Throwable $e) {
                continue;
            }

            $pagosCliente = $c->pagos->where('cliente_id', $cliente->id);
            $stats = Prestamo::calcularAtrasosHistoricos($calendario, $pagosCliente);

            $activo = in_array(strtolower($c->estado), ['entregado', 'atrasado'], true);

            // Color por número total de atrasos (regla única): verde 0 | naranja 1-4 | rojo 5+
            $color = Prestamo::clasificacionPorAtrasos($stats['total'])['hex'];

            $barras[] = [
                'grupo' => $c->id,
                'monto' => $monto,
                'fecha_entrega' => $c->fecha_entrega ? Carbon::parse($c->fecha_entrega)->format('d-m-y') : '—',
                'pago_actual' => min($stats['cubiertas'], $stats['total_cuotas']),
                'total_pagos' => $stats['total_cuotas'],
                'atrasos' => $stats['total'],
                'graves' => $stats['graves'],
                'activo' => $activo,
                'color' => $color,
                'altura_px' => $maxMonto > 0 ? max(10, (int) round(($monto / $maxMonto) * 38)) : 28,
            ];
        }

        return $barras;
    }

    public function getComportamientoColor($prestamo)
    {
        // Lógica simplificada para determinar el color del comportamiento
        // En el futuro, esto debería basarse en el historial de pagos real
        if ($prestamo->estado === 'autorizado') {
            return 'green';
        } elseif ($prestamo->estado === 'en_comite') {
            return 'orange';
        } elseif ($prestamo->estado === 'rechazado') {
            return 'red';
        }

        return 'gray';
    }

    public function autorizar()
    {
        // Verificar que el usuario sea Administrador
        if (! auth()->user()->hasRole('Administrador')) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Solo el administrador puede autorizar préstamos.',
            ]);

            return;
        }

        // Verificar que el préstamo no esté ya autorizado o rechazado
        if ($this->prestamo->estado === 'autorizado') {
            $this->dispatch('alert', [
                'type' => 'warning',
                'message' => 'Este préstamo ya ha sido autorizado.',
            ]);

            return;
        }

        if ($this->prestamo->estado === 'rechazado') {
            $this->dispatch('alert', [
                'type' => 'warning',
                'message' => 'No se puede autorizar un préstamo rechazado.',
            ]);

            return;
        }

        // Autorizar el préstamo
        $this->prestamo->autorizar(auth()->user());
        $this->prestamo->registrarBitacora('autorizado', 'Préstamo autorizado por '.auth()->user()->name);

        // Recargar el préstamo
        // $this->loadPrestamo(); No es necesario si redirigimos

        session()->flash('success', 'Préstamo autorizado exitosamente.');

        return redirect()->route('prestamos.en-comite');
    }

    public function rechazar(): void
    {
        if (! auth()->user()->hasRole('Administrador')) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Solo el administrador puede rechazar préstamos.',
            ]);

            return;
        }

        if ($this->prestamo->estado === 'rechazado') {
            $this->dispatch('alert', [
                'type' => 'warning',
                'message' => 'Este préstamo ya ha sido rechazado.',
            ]);

            return;
        }

        if ($this->prestamo->estado === 'autorizado') {
            $this->dispatch('alert', [
                'type' => 'warning',
                'message' => 'No se puede rechazar un préstamo autorizado.',
            ]);

            return;
        }

        // Requiere motivo para rechazar
        if (empty(trim($this->motivoRechazo))) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Se requiere un motivo para rechazar el préstamo.',
            ]);

            return;
        }

        $this->prestamo->rechazar(auth()->user(), $this->motivoRechazo);
        $this->prestamo->registrarBitacora('rechazado', 'Motivo: '.$this->motivoRechazo);
        // limpiar motivo después de rechazar
        $this->motivoRechazo = '';
        $this->loadPrestamo();

        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Préstamo rechazado exitosamente.',
        ]);
    }

    /**
     * Actualizar el monto autorizado para un cliente específico en préstamos grupales
     */
    public function updateMontoAutorizado(int $clienteId, $monto): void
    {
        // Verificar permisos
        if (! auth()->check() || (! auth()->user()->hasRole('Administrador') && auth()->id() !== $this->prestamo->asesor_id)) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'No tienes permiso para modificar montos autorizados.',
            ]);

            return;
        }

        // Convertir a float o null, manejando strings vacíos o cero
        $montoAutorizado = null;
        if ($monto !== null && $monto !== '' && (float) $monto > 0) {
            $montoAutorizado = (float) $monto;
        }

        // Actualizar en la tabla pivot
        $this->prestamo->clientes()->updateExistingPivot($clienteId, [
            'monto_autorizado' => $montoAutorizado,
        ]);

        // Actualizar el array local
        $this->montosAutorizados[$clienteId] = $montoAutorizado;

        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Monto autorizado actualizado correctamente.',
        ]);
    }

    /**
     * Actualizar el monto autorizado para préstamos individuales
     */
    public function updateMontoAutorizadoIndividual($monto): void
    {
        // Verificar permisos
        if (! auth()->check() || (! auth()->user()->hasRole('Administrador') && auth()->id() !== $this->prestamo->asesor_id)) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'No tienes permiso para modificar montos autorizados.',
            ]);

            return;
        }

        // Convertir a float o null, manejando strings vacíos o cero
        $montoAutorizado = null;
        if ($monto !== null && $monto !== '' && (float) $monto > 0) {
            $montoAutorizado = (float) $monto;
        }

        // Para préstamos individuales, guardamos en la tabla pivot también
        if ($this->prestamo->cliente_id) {
            $this->prestamo->clientes()->syncWithoutDetaching([
                $this->prestamo->cliente_id => ['monto_autorizado' => $montoAutorizado],
            ]);
        }

        $this->loadPrestamo();

        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Monto autorizado actualizado correctamente.',
        ]);
    }

    public function render()
    {
        return view('livewire.prestamos.show_min');
    }
}
