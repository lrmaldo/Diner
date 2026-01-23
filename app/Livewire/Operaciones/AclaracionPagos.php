<?php

namespace App\Livewire\Operaciones;

use Livewire\Component;

class AclaracionPagos extends Component
{
    public $grupoSearch = '';

    public function search()
    {
        // Lógica de búsqueda pendiente
    }

    public function render()
    {
        return view('livewire.operaciones.aclaracion-pagos');
    }
}
