<?php

namespace App\Livewire\Caja;

use Livewire\Attributes\Layout;
use Livewire\Component;

class DevolucionGarantia extends Component
{
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.caja.devolucion-garantia');
    }
}
