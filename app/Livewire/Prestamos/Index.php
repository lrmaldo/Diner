<?php

namespace App\Livewire\Prestamos;

use Livewire\Component;
use App\Models\Prestamo;

class Index extends Component
{
    public function render()
    {
        $prestamos = Prestamo::with('prestamoable')->orderByDesc('created_at')->paginate(15);
        return view('livewire.prestamos.index', compact('prestamos'));
    }

    public function autorizar(int $id)
    {
        $this->authorizeAction();
        $p = Prestamo::findOrFail($id);
        $p->autorizar(auth()->user());
        session()->flash('success', "Préstamo #{$id} autorizado");
        $this->emitSelf('refreshComponent');
    }

    public function rechazar(int $id)
    {
        $this->authorizeAction();
        $p = Prestamo::findOrFail($id);
        $p->rechazar(auth()->user());
        session()->flash('success', "Préstamo #{$id} rechazado");
        $this->emitSelf('refreshComponent');
    }

    protected function authorizeAction(): void
    {
        if (! auth()->user() || ! auth()->user()->hasRole('Administrador')) {
            abort(403);
        }
    }
}
