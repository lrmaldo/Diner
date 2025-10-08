<?php

namespace App\Livewire\Prestamos;

use App\Models\Cliente;
use App\Models\Prestamo;
use Livewire\Component;

class Show extends Component
{
    public $prestamoId;

    public $prestamo;

    public $historialPrestamos = [];

    public $porcentajeCumplimiento = 0;

    public $totalHistorial = 0;

    public $estadisticas = [
        'total_prestado' => 0,
        'total_autorizado' => 0,
        'total_rechazado' => 0,
        'promedio_monto' => 0,
        'prestamos_activos' => 0,
    ];

    public function mount($id)
    {
        $this->prestamoId = $id;
        $this->loadPrestamo();
        $this->loadHistorial();
    }

    public function loadPrestamo()
    {
        $this->prestamo = Prestamo::with(['cliente', 'clientes', 'representante', 'autorizador'])
            ->findOrFail($this->prestamoId);
    }

    public function loadHistorial()
    {
        // Obtener el historial según el tipo de préstamo
        if ($this->prestamo->producto === 'individual' && $this->prestamo->cliente_id) {
            $clienteId = $this->prestamo->cliente_id;

            // Buscar TODOS los préstamos anteriores del mismo cliente (sin límite)
            $this->historialPrestamos = Prestamo::where('cliente_id', $clienteId)
                ->where('id', '!=', $this->prestamoId)
                ->orderByDesc('created_at')
                ->get();
        } elseif ($this->prestamo->producto === 'grupal' && $this->prestamo->representante_id) {
            $representanteId = $this->prestamo->representante_id;

            // Buscar TODOS los préstamos anteriores del representante
            $this->historialPrestamos = Prestamo::where('representante_id', $representanteId)
                ->where('id', '!=', $this->prestamoId)
                ->orderByDesc('created_at')
                ->get();
        }

        $this->totalHistorial = $this->historialPrestamos->count();

        // Calcular estadísticas del historial
        if ($this->totalHistorial > 0) {
            $this->estadisticas['total_prestado'] = $this->historialPrestamos->sum('monto_total');
            $this->estadisticas['total_autorizado'] = $this->historialPrestamos->where('estado', 'autorizado')->count();
            $this->estadisticas['total_rechazado'] = $this->historialPrestamos->where('estado', 'rechazado')->count();
            $this->estadisticas['promedio_monto'] = $this->historialPrestamos->avg('monto_total');
            $this->estadisticas['prestamos_activos'] = $this->historialPrestamos->whereIn('estado', ['en_curso', 'en_revision', 'autorizado'])->count();

            // Calcular porcentaje de cumplimiento
            $autorizados = $this->estadisticas['total_autorizado'];
            $this->porcentajeCumplimiento = round(($autorizados / $this->totalHistorial) * 100);
        }
    }

    public function getComportamientoColor($prestamo)
    {
        // Lógica simplificada para determinar el color del comportamiento
        // En el futuro, esto debería basarse en el historial de pagos real
        if ($prestamo->estado === 'autorizado') {
            return 'green';
        } elseif ($prestamo->estado === 'en_revision') {
            return 'orange';
        } elseif ($prestamo->estado === 'rechazado') {
            return 'red';
        }

        return 'gray';
    }

    public function render()
    {
        return view('livewire.prestamos.show');
    }
}
