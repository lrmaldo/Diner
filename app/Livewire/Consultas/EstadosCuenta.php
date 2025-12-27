<?php

namespace App\Livewire\Consultas;

use App\Models\Cliente;
use App\Models\Grupo;
use App\Models\Prestamo;
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
                ->with(['grupo'])
                ->orderBy('id', 'desc')
                ->get();
        }
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
