<?php

namespace App\Livewire\Prestamos;

use Livewire\Component;
use App\Models\Prestamo;
use App\Models\Cliente;
use App\Models\Grupo;

class Edit extends Component
{
    public $prestamo_id;
    public $step = 2;
    public $producto;
    public $plazo;
    public $periodicidad;
    public $periodo_pago;
    public $dia_pago;
    public $fecha_entrega;
    public $fecha_primer_pago;
    public $tasa_interes;

    public $cliente_id;
    public $grupo_id;
    public $monto_total;

    // UX helpers
    public $clienteSearch = '';
    public $grupoSearch = '';

    // Modal controls
    public $showClienteModal = false;
    public $showGrupoModal = false;

    // Selected names for badge
    public $cliente_nombre_selected;
    public $grupo_nombre_selected;

    // clientes agregados (solo para grupal)
    public $clientesAgregados = [];

    // Inline create
    public $showNewClienteForm = false;
    public $new_apellido_paterno;
    public $new_apellido_materno;
    public $new_nombres;
    public $new_curp;

    public $showNewGrupoForm = false;
    public $new_grupo_nombre;
    public $new_grupo_descripcion;

    // expose admin flag to view
    public $isAdmin = false;

    public function mount(Prestamo $prestamo): void
    {
        $this->isAdmin = auth()->check() && auth()->user()->hasRole('Administrador');

        $this->prestamo_id = $prestamo->id;
        $this->producto = $prestamo->producto;
        $this->plazo = $prestamo->plazo;
        $this->periodicidad = $prestamo->periodicidad;
        $this->periodo_pago = $prestamo->periodo_pago ?? null;
        $this->fecha_entrega = $prestamo->fecha_entrega ? $prestamo->fecha_entrega->toDateString() : null;
        $this->fecha_primer_pago = $prestamo->fecha_primer_pago ? $prestamo->fecha_primer_pago->toDateString() : null;
        $this->dia_pago = $prestamo->dia_pago;
        $this->tasa_interes = $prestamo->tasa_interes;
        $this->cliente_id = $prestamo->cliente_id;
        $this->grupo_id = $prestamo->grupo_id;
        $this->monto_total = $prestamo->monto_total;

        // load clientesAgregados if grupal
        if ($prestamo->producto === 'grupal') {
            $this->clientesAgregados = $prestamo->clientes()->get()->map(function ($c) {
                return ['cliente_id' => $c->id, 'monto_solicitado' => $c->pivot->monto_solicitado ?? null, 'nombre' => trim("{$c->nombres} {$c->apellido_paterno}")];
            })->toArray();
        }
    }

    public function render()
    {
        $clientesQuery = Cliente::query();
        if ($this->clienteSearch) {
            $term = $this->clienteSearch;
            $clientesQuery->where(function ($q) use ($term) {
                $q->where('nombres', 'like', "%{$term}%")
                  ->orWhere('apellido_paterno', 'like', "%{$term}%")
                  ->orWhere('apellido_materno', 'like', "%{$term}%");
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
            });
        }

        $gruposQuery = Grupo::query();
        if ($this->grupoSearch) {
            $t = $this->grupoSearch;
            $gruposQuery->where('nombre', 'like', "%{$t}%");
            if (is_numeric($t)) {
                $gruposQuery->orWhere('id', (int) $t);
            }
        }

        $clientes = $clientesQuery->orderBy('nombres')->limit(10)->get();
        $grupos = $gruposQuery->orderBy('nombre')->limit(10)->get();

        // set selected names for badges
        if ($this->cliente_id) {
            $c = Cliente::find($this->cliente_id);
            $this->cliente_nombre_selected = $c ? trim("{$c->nombres} {$c->apellido_paterno} {$c->apellido_materno}") : null;
        } else {
            $this->cliente_nombre_selected = null;
        }

        if ($this->grupo_id) {
            $g = Grupo::find($this->grupo_id);
            $this->grupo_nombre_selected = $g ? $g->nombre : null;
        } else {
            $this->grupo_nombre_selected = null;
        }

        $prestamo = Prestamo::find($this->prestamo_id);

        return view('livewire.prestamos.create', compact('clientes', 'grupos', 'prestamo'));
    }

    protected function rules(): array
    {
        $isAdmin = auth()->check() && auth()->user()->hasRole('Administrador');

        $rules = [
            'producto' => ['required', 'in:individual,grupal'],
            'plazo' => ['required', 'in:4meses,4mesesD,5mesesD,6meses,1ano'],
            'periodicidad' => ['required', 'in:semanal,catorcenal,quincenal'],
            'dia_pago' => ['required', 'in:lunes,martes,miercoles,jueves,viernes'],
            'fecha_entrega' => ['required', 'date'],
            'fecha_primer_pago' => ['nullable', 'date'],
        ];

        if ($isAdmin) {
            $rules['tasa_interes'] = ['required', 'numeric', 'min:0'];
        } else {
            $rules['tasa_interes'] = ['nullable', 'numeric', 'min:0'];
        }

        return $rules;
    }

    protected function validateFirstStep(): array
    {
        $fields = ['producto','plazo','periodicidad','fecha_entrega','fecha_primer_pago','dia_pago'];
        $allRules = method_exists($this, 'rules') ? $this->rules() : (property_exists($this, 'rules') ? $this->rules : []);

        $rulesSubset = [];
        foreach ($fields as $f) {
            if (isset($allRules[$f])) {
                $rulesSubset[$f] = $allRules[$f];
            }
        }

        return $this->validate($rulesSubset);
    }

    protected function validateFechaPrimerPago(): void
    {
        if (! $this->fecha_primer_pago) {
            return;
        }

        $entrega = \Carbon\Carbon::parse($this->fecha_entrega);
        $primer = \Carbon\Carbon::parse($this->fecha_primer_pago);

        if ($primer->lt($entrega)) {
            $this->addError('fecha_primer_pago', 'La fecha del primer pago no puede ser anterior a la fecha de entrega.');
            return;
        }

        $period = $this->periodicidad ?? $this->periodo_pago;
        $periodDays = match($period) {
            'semanal' => 7,
            'catorcenal' => 14,
            'quincenal' => 15,
            default => 0,
        };

        if ($periodDays <= 0) {
            return;
        }

        $maxAllowed = $entrega->copy()->addDays($periodDays + 2);
        if ($primer->gt($maxAllowed)) {
            $this->addError('fecha_primer_pago', "La fecha del primer pago debe estar dentro de los {$periodDays} días desde la entrega más 2 días de gracia.");
        }
    }

    public function updatePrestamo(): void
    {
        if (! $this->prestamo_id) {
            $this->addError('prestamo', 'No hay préstamo cargado para actualizar.');
            return;
        }

        $this->validateFirstStep();
        $this->validateFechaPrimerPago();

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $prestamo = Prestamo::findOrFail($this->prestamo_id);

        $prestamo->producto = $this->producto;
        $prestamo->plazo = $this->plazo;
        $prestamo->periodicidad = $this->periodicidad;
        $prestamo->fecha_entrega = $this->fecha_entrega;
        $prestamo->fecha_primer_pago = $this->fecha_primer_pago;
        $prestamo->dia_pago = $this->dia_pago;

        if ($this->isAdmin) {
            $prestamo->tasa_interes = $this->tasa_interes;
        }

        $prestamo->save();

        session()->flash('success', 'Préstamo actualizado correctamente.');
    }

    /**
     * Para compatibilidad con la vista compartida: avanzar a vinculación
     */
    public function crearPrestamo(): void
    {
        // asegurarnos que haya un prestamo cargado
        if (! $this->prestamo_id) {
            $this->addError('prestamo', 'No hay préstamo cargado');
            return;
        }

        $this->step = 2;
        session()->flash('success', 'Continuando a vinculación');
    }

    public function linkClienteIndividual(float $monto)
    {
        if (! isset($this->prestamo_id)) {
            $this->addError('prestamo', 'Primero crea el préstamo');
            return;
        }
        $this->validate(['cliente_id' => ['required','exists:clientes,id']]);

        $prestamo = Prestamo::findOrFail($this->prestamo_id);
        $prestamo->cliente_id = $this->cliente_id;
        $prestamo->monto_total = $monto;
        $prestamo->save();

        session()->flash('success', 'Cliente vinculado al préstamo');
        return redirect()->route('prestamos.index');
    }

    public function agregarClienteAlGrupo(int $clienteId, float $monto)
    {
        if (! isset($this->prestamo_id)) {
            $this->addError('prestamo', 'Primero crea el préstamo');
            return;
        }

        $prestamo = Prestamo::findOrFail($this->prestamo_id);
        $cliente = Cliente::findOrFail($clienteId);
        $prestamo->clientes()->syncWithoutDetaching([$cliente->id => ['monto_solicitado' => $monto]]);

        $this->clientesAgregados[] = ['cliente_id' => $cliente->id, 'monto_solicitado' => $monto, 'nombre' => trim("{$cliente->nombres} {$cliente->apellido_paterno}")];
    }

    public function selectCliente(int $id): void
    {
        $this->cliente_id = $id;
        $this->clienteSearch = '';
        $cliente = Cliente::find($id);
        $this->cliente_nombre_selected = $cliente ? trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}") : null;
        $this->showClienteModal = false;
    }

    public function selectGrupo(int $id): void
    {
        $this->grupo_id = $id;
        $this->grupoSearch = '';
        $grupo = Grupo::find($id);
        $this->grupo_nombre_selected = $grupo ? $grupo->nombre : null;
        $this->showGrupoModal = false;
        if ($grupo) {
            $this->clientesAgregados = $grupo->clientes()->get()->map(function ($c) {
                return ['cliente_id' => $c->id, 'monto_solicitado' => $c->pivot->monto_solicitado ?? null, 'nombre' => trim("{$c->nombres} {$c->apellido_paterno}")];
            })->toArray();
        }
    }

    public function addNewClient()
    {
        $data = $this->validate([
            'new_apellido_paterno' => ['required', 'string', 'max:255'],
            'new_apellido_materno' => ['nullable', 'string', 'max:255'],
            'new_nombres' => ['required', 'string', 'max:255'],
            'new_curp' => ['nullable', 'string', 'max:18'],
        ]);

        $cliente = Cliente::create([
            'apellido_paterno' => $this->new_apellido_paterno,
            'apellido_materno' => $this->new_apellido_materno,
            'nombres' => $this->new_nombres,
            'curp' => $this->new_curp,
        ]);

        $this->cliente_id = $cliente->id;
        $this->showNewClienteForm = false;
        $this->new_apellido_paterno = $this->new_apellido_materno = $this->new_nombres = $this->new_curp = null;
        session()->flash('success', 'Cliente creado y seleccionado');
    }

    public function addNewGrupo()
    {
        $data = $this->validate([
            'new_grupo_nombre' => ['required', 'string', 'max:255'],
            'new_grupo_descripcion' => ['nullable', 'string', 'max:1000'],
        ]);

        $grupo = Grupo::create([
            'nombre' => $this->new_grupo_nombre,
            'descripcion' => $this->new_grupo_descripcion,
        ]);

        $this->grupo_id = $grupo->id;
        $this->showNewGrupoForm = false;
        $this->new_grupo_nombre = $this->new_grupo_descripcion = null;
        session()->flash('success', 'Grupo creado y seleccionado');
        $this->showClienteModal = true;
    }
}
