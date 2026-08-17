<?php

namespace App\Livewire\Consultas;

use App\Models\Cliente;
use App\Models\Grupo;
use App\Models\Prestamo;
use App\Services\CalculadoraPrestamos;
use Livewire\Component;

class EstadosCuenta extends Component
{
    public $grupo;
    public $nombre;
    public $results = [];
    public $selectedClient = null;
    public $clientLoans = [];
    public $selectedLoan = null;
    public $noResults = false;
    public $volverUrl = null;

    public function mount()
    {
        // Deep-link desde comité: ?cliente=ID abre directo el estado de cuenta del cliente,
        // y ?volver=URL permite regresar a la vista anterior (el comité del préstamo).
        $this->volverUrl = request()->query('volver');

        $clienteId = request()->query('cliente');
        if ($clienteId) {
            $this->selectClient((int) $clienteId);
        }
    }

    public function updatedGrupo()
    {
        $this->buscarPorGrupo();
    }

    public function updatedNombre()
    {
        $this->buscarPorNombre();
    }

    public function buscarPorGrupo()
    {
        $this->reset(['results', 'selectedClient', 'clientLoans', 'selectedLoan', 'nombre', 'noResults']);
        
        if (empty($this->grupo)) {
            return;
        }

        // Buscar prestamo por ID (que el usuario llama "numero de grupo")
        $prestamo = Prestamo::with(['clientes', 'cliente'])->find($this->grupo);

        if ($prestamo) {
            if ($prestamo->producto === 'grupal') {
                $this->results = $prestamo->clientes;
            } else {
                // Si es individual, verificamos si tiene cliente directo o en relación muchos a muchos
                if ($prestamo->cliente) {
                    $this->results = collect([$prestamo->cliente]);
                } elseif ($prestamo->clientes->isNotEmpty()) {
                    $this->results = $prestamo->clientes;
                } else {
                    $this->results = [];
                }
            }
        } else {
            $this->results = [];
        }

        if (empty($this->results) || (is_countable($this->results) && count($this->results) == 0)) {
            $this->noResults = true;
        }
    }

    public function buscarPorNombre()
    {
        $this->reset(['results', 'selectedClient', 'clientLoans', 'selectedLoan', 'grupo', 'noResults']);

        if (empty($this->nombre)) {
            return;
        }

        $this->results = Cliente::where('nombres', 'like', '%' . $this->nombre . '%')
            ->orWhere('apellido_paterno', 'like', '%' . $this->nombre . '%')
            ->orWhere('apellido_materno', 'like', '%' . $this->nombre . '%')
            ->get();

        if ($this->results->isEmpty()) {
            $this->noResults = true;
        }
    }

    public function selectClient($clientId)
    {
        $this->selectedClient = Cliente::with('telefonos')->find($clientId);
        $this->selectedLoan = null;
        
        if ($this->selectedClient) {
            $this->clientLoans = Prestamo::where('cliente_id', $clientId)
                ->orWhereHas('clientes', function ($query) use ($clientId) {
                    $query->where('clientes.id', $clientId);
                })
                ->with(['grupo', 'pagos', 'clientes'])
                ->orderBy('id', 'desc')
                ->get();
        }
    }

    /**
     * Clasificación de color de un crédito por su número de atrasos, para colorear la fila.
     * Sólo aplica a créditos ya entregados/finalizados; los demás quedan sin color.
     *
     * @return array{nivel: string, hex: string, row: string}
     */
    public function clasificacionAtrasos($loan): array
    {
        if (! in_array(strtolower($loan->estado ?? ''), ['entregado', 'atrasado', 'liquidado', 'pagado', 'castigado'], true)) {
            return ['nivel' => 'na', 'hex' => '#9ca3af', 'row_bg' => ''];
        }

        $clienteId = $this->selectedClient?->id;

        // Monto de este cliente en el crédito (para grupales usa su porción del pivot)
        $monto = $loan->monto_total;
        if ($clienteId) {
            $enPivot = $loan->clientes->firstWhere('id', $clienteId);
            if ($enPivot && $enPivot->pivot) {
                $monto = $enPivot->pivot->monto_autorizado ?? $enPivot->pivot->monto_solicitado ?? $loan->monto_total;
            }
        }

        try {
            $calendario = CalculadoraPrestamos::calcularCalendarioPagos(
                $monto,
                $loan->tasa_interes,
                $loan->plazo,
                $loan->periodicidad,
                $loan->fecha_primer_pago ?? $loan->fecha_entrega,
                $loan->ultimo_pago ?? null
            );
        } catch (\Throwable $e) {
            return ['nivel' => 'na', 'hex' => '#9ca3af', 'row_bg' => ''];
        }

        $pagos = $clienteId ? $loan->pagos->where('cliente_id', $clienteId) : $loan->pagos;
        $stats = Prestamo::calcularAtrasosHistoricos($calendario, $pagos);

        return Prestamo::clasificacionPorAtrasos($stats['total']);
    }

    public function selectLoan($loanId)
    {
        $this->selectedLoan = Prestamo::with(['pagos' => function($query) {
            $query->orderBy('fecha_pago', 'asc');
        }, 'cliente', 'grupo'])->find($loanId);
    }

    public function resetLoan()
    {
        $this->selectedLoan = null;
    }

    public function resetSearch()
    {
        $this->selectedClient = null;
        $this->clientLoans = [];
        $this->selectedLoan = null;
    }

    public function render()
    {
        return view('livewire.consultas.estados-cuenta');
    }
}
