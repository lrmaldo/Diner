<?php

namespace App\Livewire\Prestamos;

use Livewire\Component;
use App\Models\Prestamo;
use App\Models\Cliente;
use App\Models\Grupo;

class Create extends Component
{
    public $tipo = 'cliente'; // 'cliente' o 'grupo'
    public $step = 1;
    public $producto = 'individual'; // 'individual' o 'grupal'
    public $prestamo_id;
    public $cliente_id;
    public $grupo_id;
    public $monto;
    public $monto_total;
    public $plazo = '4meses';
    public $periodo_pago = 'semana';
    public $periodicidad = 'semanal';
    public $dia_pago = 'martes';
    public $fecha_entrega;
    public $tasa_interes = 4.5;
    public $fecha_primer_pago;
    // UX helpers
    public $clienteSearch = '';
    public $grupoSearch = '';

    // Modal controls
    public $showClienteModal = false;
    public $showGrupoModal = false;

    // Modal filters (extra)
    public $cliente_filter_curp = '';
    public $cliente_filter_apellido = '';

    // Selected names for badge
    public $cliente_nombre_selected;
    public $grupo_nombre_selected;

    // clientes agregados (solo para grupal)
    public $clientesAgregados = []; // array of ['cliente_id'=>, 'monto_solicitado'=>]

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

    public function mount($prestamo = null): void
    {
        $this->isAdmin = auth()->check() && auth()->user()->hasRole('Administrador');

        // If a numeric id or route-binding provided, try to resolve the model
        if ($prestamo && ! ($prestamo instanceof \App\Models\Prestamo)) {
            $prestamo = Prestamo::find($prestamo);
        }

        if ($prestamo instanceof \App\Models\Prestamo) {
            $this->prestamo_id = $prestamo->id;
            $this->producto = $prestamo->producto;
            $this->plazo = $prestamo->plazo ?? $this->plazo;
            $this->periodicidad = $prestamo->periodicidad ?? $this->periodicidad;
            $this->fecha_entrega = $prestamo->fecha_entrega ? $prestamo->fecha_entrega->toDateString() : $this->fecha_entrega;
            $this->fecha_primer_pago = $prestamo->fecha_primer_pago ? $prestamo->fecha_primer_pago->toDateString() : null;
            $this->dia_pago = $prestamo->dia_pago ?? $this->dia_pago;
            $this->tasa_interes = $prestamo->tasa_interes ?? $this->tasa_interes;

            // If prestamo exists but clients not linked (or missing), go to step 2 to continue
            $this->step = 2;
        }
    }

    public function render()
    {
        // ensure default fecha_entrega is today when not set
        if (empty($this->fecha_entrega)) {
            $this->fecha_entrega = now()->toDateString();
        }
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

        // additional modal filters
        if ($this->cliente_filter_curp) {
            $clientesQuery->where('curp', 'like', "%{$this->cliente_filter_curp}%");
        }
        if ($this->cliente_filter_apellido) {
            $clientesQuery->where(function ($q) {
                $q->where('apellido_paterno', 'like', "%{$this->cliente_filter_apellido}%")
                  ->orWhere('apellido_materno', 'like', "%{$this->cliente_filter_apellido}%");
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

        $prestamo = null;
        if ($this->prestamo_id) {
            $prestamo = Prestamo::find($this->prestamo_id);
        }

        return view('livewire.prestamos.create', compact('clientes', 'grupos', 'prestamo'));
    }

    protected function rules(): array
    {
        $isAdmin = auth()->check() && auth()->user()->hasRole('Administrador');

        $rules = [
            'tipo' => ['required', 'in:cliente,grupo'],
            'producto' => ['required', 'in:individual,grupal'],
            //'cliente_id' => ['required_if:tipo,cliente', 'exists:clientes,id'],
            //'grupo_id' => ['required_if:tipo,grupo', 'exists:grupos,id'],

            'plazo' => ['required', 'in:4meses,4mesesD,5mesesD,6meses,1ano'],
            'periodicidad' => ['required', 'in:semanal,catorcenal,quincenal'],
            'dia_pago' => ['required', 'in:lunes,martes,miercoles,jueves,viernes'],
            'fecha_entrega' => ['required', 'date'],
            'fecha_primer_pago' => ['nullable', 'date'],
        ];

        if ($isAdmin) {
            $rules['tasa_interes'] = ['required', 'numeric', 'min:0'];
        } else {
            // non-admins: allow numeric but we'll ignore and enforce default on save
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

        // regla avanzada: fecha_primer_pago debe estar entre fecha_entrega y fecha_entrega + periodo + 2 dias
        $entrega = \Carbon\Carbon::parse($this->fecha_entrega);
        $primer = \Carbon\Carbon::parse($this->fecha_primer_pago);

        if ($primer->lt($entrega)) {
            $this->addError('fecha_primer_pago', 'La fecha del primer pago no puede ser anterior a la fecha de entrega.');
            return;
        }

        // determinar periodicidad: preferir property periodicidad si existe
        $period = $this->periodicidad ?? $this->periodo_pago;
        $periodDays = match($period) {
            'semanal' => 7,
            'catorcenal' => 14,
            'quincenal' => 15,
            default => 0,
        };

        if ($periodDays <= 0) {
            // no hay regla aplicable
            return;
        }

        $maxAllowed = $entrega->copy()->addDays($periodDays + 2);
        if ($primer->gt($maxAllowed)) {
            $this->addError('fecha_primer_pago', "La fecha del primer pago debe estar dentro de los {$periodDays} días desde la entrega más 2 días de gracia.");
        }
    }

    public function crearPrestamo()
    {
        // validar primer paso
        $this->validateFirstStep();
        $this->validateFechaPrimerPago();

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $data = [
            'producto' => $this->producto,
            'plazo' => $this->plazo,
            'periodicidad' => $this->periodicidad,
            'fecha_entrega' => $this->fecha_entrega,
            'fecha_primer_pago' => $this->fecha_primer_pago,
            'dia_pago' => $this->dia_pago,
            'estado' => 'en_curso',
        ];

        // only allow overriding tasa_interes if user is admin
        if (auth()->check() && auth()->user()->hasRole('Administrador')) {
            $data['tasa_interes'] = $this->tasa_interes;
        } else {
            // ensure default is used
            $data['tasa_interes'] = 4.5;
            $this->tasa_interes = 4.5;
        }

        $prestamo = Prestamo::create($data);

        $this->prestamo_id = $prestamo->id;
        $this->step = 2;
        session()->flash('success', 'Préstamo creado con folio: ' . $prestamo->folio);
    }

    public function updatedClienteSearch()
    {
        // trigger render to refresh $this->clientes
    }

    public function updatedGrupoSearch()
    {
        // trigger render to refresh $this->grupos
    }

    public function submit()
    {
        $this->validate();

        $prestamo = new Prestamo();
        $prestamo->monto = $this->monto;
        $prestamo->plazo = $this->plazo;
        $prestamo->periodo_pago = $this->periodo_pago;
        $prestamo->dia_pago = $this->dia_pago;
        $prestamo->fecha_entrega = $this->fecha_entrega;
        $prestamo->tasa_interes = $this->tasa_interes;
        $prestamo->estado = 'en_curso';

        if ($this->tipo === 'cliente') {
            $cliente = Cliente::findOrFail($this->cliente_id);
            $cliente->prestamos()->save($prestamo);
        } else {
            $grupo = Grupo::findOrFail($this->grupo_id);
            $grupo->prestamos()->save($prestamo);
        }

        session()->flash('success', 'Solicitud de préstamo creada con estado en_curso');
        return redirect()->route('prestamos.index');
    }
}
