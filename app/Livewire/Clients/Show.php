<?php

namespace App\Livewire\Clients;

use App\Models\Cliente;
use Livewire\Component;

class Show extends Component
{
    public Cliente $cliente;

    public function mount(Cliente $cliente)
    {
        $this->cliente = $cliente;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.clients.show', [
            'cliente' => $this->cliente,
        ]);
    }
}
