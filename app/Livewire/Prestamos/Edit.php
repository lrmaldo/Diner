<?php

namespace App\Livewire\Prestamos;

use App\Models\Cliente;
use App\Models\Grupo;
use App\Models\Prestamo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

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

    public $garantia = 10.00;

    public $asesor_id;

    public $asesorSearch = '';

    public $asesorSelected;

    public $asesores = [];

    public $cliente_id;

    public $grupo_id;

    public $monto_total;

    // UX helpers
    public $clienteSearch = '';

    public $grupoSearch = '';

    // Modal controls
    public $showClienteModal = false;

    public $showGrupoModal = false;

    public $showEditClienteModal = false;

    // Selected names for badge
    public $cliente_nombre_selected;

    public $grupo_nombre_selected;

    // clientes agregados (solo para grupal)
    public $clientesAgregados = [];

    public $representante_id = null;

    // Inline create
    public $showNewClienteForm = false;

    public $new_apellido_paterno;

    public $new_apellido_materno;

    public $new_nombres;

    public $new_curp;

    public $new_email;

    public $new_pais_nacimiento;

    public $new_nombre_conyuge;

    public $new_calle_numero;

    public $new_referencia_domiciliaria;

    public $new_estado_civil;

    public $new_dependientes_economicos;

    public $new_nombre_aval;

    public $new_actividad_productiva;

    public $new_anios_experiencia;

    public $new_ingreso_mensual;

    public $new_gasto_mensual_familiar;

    public $new_credito_solicitado;

    public $new_estado;

    public $new_municipio;

    public $new_colonia;

    public $new_codigo_postal;

    public $showNewGrupoForm = false;

    public $new_grupo_nombre;

    public $new_grupo_descripcion;

    public $suggested_grupo_name;

    public $group_name_suggestions = [];

    // expose admin flag to view
    public $isAdmin = false;

    // status alert for simple feedback
    public $status_message = null;

    public $status_type = 'success';

    // Para compatibilidad con la vista compartida create.blade.php
    public $updateCounter = 0;

    // Compatibilidad con vista compartida (botón individual usa $monto)
    public $monto = null;

    // Comentarios para el comité
    public $comentarios_comite = '';

    public function mount(Prestamo $prestamo): void
    {
        $this->isAdmin = auth()->check() && (auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Asesor'));

        $this->prestamo_id = $prestamo->id;
        $this->producto = $prestamo->producto;
        $this->plazo = $prestamo->plazo;
        $this->periodicidad = $prestamo->periodicidad;
        $this->periodo_pago = $prestamo->periodo_pago ?? null;
        $this->fecha_entrega = $prestamo->fecha_entrega ? $prestamo->fecha_entrega->toDateString() : null;
        $this->fecha_primer_pago = $prestamo->fecha_primer_pago ? $prestamo->fecha_primer_pago->toDateString() : null;
        $this->dia_pago = $prestamo->dia_pago;
        $this->tasa_interes = $prestamo->tasa_interes;
        $this->garantia = $prestamo->garantia ?? $this->garantia;
        $this->comentarios_comite = $prestamo->comentarios_comite ?? '';

        // Cargar lista de asesores disponibles
        $this->asesores = User::whereHas('roles', function ($query) {
            $query->where('name', 'Asesor');
        })->get();

        // Cargar datos del asesor si está asignado
        if ($prestamo->asesor_id) {
            $this->asesor_id = $prestamo->asesor_id;
            $asesor = User::find($prestamo->asesor_id);
            if ($asesor) {
                $this->asesorSelected = [
                    'id' => $asesor->id,
                    'name' => $asesor->name,
                    'email' => $asesor->email,
                ];
            }
        }

        $this->cliente_id = $prestamo->cliente_id;
        $this->grupo_id = $prestamo->grupo_id;
        $this->monto_total = $prestamo->monto_total;

        // Para compatibilidad con vista compartida (préstamos individuales usan $monto)
        $this->monto = $prestamo->monto_total;

        $this->representante_id = $prestamo->representante_id;

        // load clientesAgregados if grupal y asignar grupo automático si falta
        if ($prestamo->producto === 'grupal') {
            if (empty($this->grupo_id)) {
                $grupo = $this->ensureAutoGrupoExists();
                $prestamo->grupo_id = $grupo->id;
                $prestamo->save();
                $this->grupo_id = $grupo->id;
                $this->grupo_nombre_selected = $grupo->nombre;
            }
            $this->clientesAgregados = $prestamo->clientes()->get()->map(function ($c) {
                return ['cliente_id' => $c->id, 'monto_solicitado' => $c->pivot->monto_solicitado ?? null, 'nombre' => trim("{$c->nombres} {$c->apellido_paterno}")];
            })->toArray();
        } elseif ($prestamo->producto === 'individual' && $prestamo->cliente_id) {
            // Cargar nombre del cliente para préstamos individuales
            $cliente = Cliente::find($prestamo->cliente_id);
            if ($cliente) {
                $this->cliente_nombre_selected = trim("{$cliente->nombres} {$cliente->apellido_paterno}");
            }
        }
    }

    protected function ensureAutoGrupoExists(): Grupo
    {
        $base = 'representante grupal';
        $name = $base;
        $i = 1;
        while (Grupo::where('nombre', $name)->exists()) {
            $name = $base.' '.$i;
            $i++;
        }

        return Grupo::create([
            'nombre' => $name,
            'descripcion' => 'Grupo autogenerado al abrir edición',
        ]);
    }

    public function render()
    {
        // Los asesores ya se cargan en mount() y se actualizan con searchAsesores()
        $asesores = $this->asesores;

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

        return view('livewire.prestamos.create', compact('clientes', 'grupos', 'prestamo', 'asesores'));
    }

    protected function rules(): array
    {
        $isAdmin = auth()->check() && (auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Asesor'));

        $rules = [
            'producto' => ['required', 'in:individual,grupal'],
            'plazo' => ['required', 'in:4meses,4mesesD,5mesesD,6meses,1ano'],
            'periodicidad' => ['required', 'in:semanal,catorcenal,quincenal'],
            'dia_pago' => ['required', 'in:lunes,martes,miercoles,jueves,viernes'],
            'fecha_entrega' => ['required', 'date'],
            'fecha_primer_pago' => ['nullable', 'date'],
            'garantia' => ['required', 'numeric', 'min:0', 'max:100'],
            'asesor_id' => ['nullable', 'exists:users,id'],
            'comentarios_comite' => ['nullable', 'string', 'max:1000'],
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
        $fields = ['producto', 'plazo', 'periodicidad', 'fecha_entrega', 'fecha_primer_pago', 'dia_pago', 'garantia', 'asesor_id', 'comentarios_comite'];
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
        $periodDays = match ($period) {
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
        $prestamo->garantia = $this->garantia;
        $prestamo->asesor_id = $this->asesor_id;
        $prestamo->comentarios_comite = $this->comentarios_comite;

        if ($this->isAdmin) {
            $prestamo->tasa_interes = $this->tasa_interes;
        }

        $prestamo->save();

        // Avanzar al paso 2 después de actualizar
        $this->step = 2;
        $this->showMessage('success', 'Préstamo actualizado correctamente. Continuando a agregar clientes.');
    }

    /**
     * Método dedicado para mostrar mensajes con actualización forzada
     */
    public function showMessage(string $type, string $message): void
    {
        $this->status_type = $type;
        $this->status_message = $message;
        $this->updateCounter++; // Forzar actualización
        $this->dispatch('prestamo-actualizado');
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
        $this->showMessage('success', 'Continuando a agregar clientes');
    }

    public function linkClienteIndividual(float $monto)
    {
        if (! isset($this->prestamo_id)) {
            $this->addError('prestamo', 'Primero crea el préstamo');

            return;
        }
        $this->validate(['cliente_id' => ['required', 'exists:clientes,id']]);

        $prestamo = Prestamo::findOrFail($this->prestamo_id);
        $prestamo->cliente_id = $this->cliente_id;
        $prestamo->monto_total = $monto;
        $prestamo->save();

        $this->showMessage('success', 'Cliente vinculado al préstamo');

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
        // attach or update pivot without creating duplicates in the local array
        $prestamo->clientes()->syncWithoutDetaching([$cliente->id => ['monto_solicitado' => $monto]]);

        // ensure clientesAgregados is normalized before searching/updating
        $this->normalizeClientesAgregados();

        // find existing index in clientesAgregados
        $index = null;
        foreach ($this->clientesAgregados as $i => $row) {
            if (is_array($row) && isset($row['cliente_id']) && $row['cliente_id'] == $cliente->id) {
                $index = $i;
                break;
            }
        }

        $entry = ['cliente_id' => $cliente->id, 'monto_solicitado' => $monto, 'nombre' => trim("{$cliente->nombres} {$cliente->apellido_paterno}")];

        if (is_null($index)) {
            $this->clientesAgregados[] = $entry;
        } else {
            // replace existing entry (preserve order)
            $this->clientesAgregados[$index] = $entry;
        }

        $this->dispatch('miembroGuardado', [
            'success' => true,
            'message' => 'Miembro guardado correctamente.',
            'cliente_id' => $cliente->id,
            'monto' => $monto,
        ]);

        // also set component-level status for blade component
        $this->status_message = 'Miembro guardado correctamente.';
        $this->status_type = 'success';
    }

    public function selectCliente(int $id): void
    {
        $this->cliente_id = $id;
        $this->clienteSearch = '';
        $cliente = Cliente::find($id);
        $this->cliente_nombre_selected = $cliente ? trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}") : null;
        $this->showClienteModal = false;
        // Abrir modal de edición para corroborar datos y capturar monto
        if ($cliente) {
            $this->openEditCliente($cliente->id);
        }
        if ($this->grupo_id) {
            $this->normalizeClientesAgregados();
            $exists = collect($this->clientesAgregados)->first(fn ($r) => is_array($r) && isset($r['cliente_id']) && $r['cliente_id'] == $cliente->id);
            if (! $exists) {
                $this->clientesAgregados[] = ['cliente_id' => $cliente->id, 'monto_solicitado' => null, 'nombre' => trim("{$cliente->nombres} {$cliente->apellido_paterno}")];
            }
            if ($this->prestamo_id && $cliente) {
                $prestamo = Prestamo::find($this->prestamo_id);
                if ($prestamo) {
                    $prestamo->clientes()->syncWithoutDetaching([$cliente->id => ['monto_solicitado' => 0]]);
                }
            }
        }
    }

    public function guardarMiembro(int $index): void
    {
        $this->normalizeClientesAgregados();

        if (! isset($this->clientesAgregados[$index])) {
            $this->addError('miembro', 'Índice de miembro inválido');

            return;
        }

        $row = $this->clientesAgregados[$index];
        $clienteId = $row['cliente_id'] ?? null;
        $monto = $row['monto_solicitado'] ?? 0;

        // validation: monto must be numeric and greater than zero
        if (! is_numeric($monto) || (float) $monto <= 0) {
            $this->addError('miembro', 'El monto solicitado debe ser un número mayor a 0.');

            return;
        }

        if (! $clienteId) {
            $this->addError('miembro', 'Cliente inválido');

            return;
        }

        $this->agregarClienteAlGrupo($clienteId, (float) $monto);
    }

    /**
     * Elimina un miembro de la lista temporal por índice y limpia representante si aplica.
     */
    public function eliminarMiembro(int $index): void
    {
        $this->normalizeClientesAgregados();

        if (! isset($this->clientesAgregados[$index])) {
            return;
        }

        $row = $this->clientesAgregados[$index];

        unset($this->clientesAgregados[$index]);
        $this->clientesAgregados = array_values($this->clientesAgregados);

        if (isset($row['cliente_id']) && (int) $this->representante_id === (int) $row['cliente_id']) {
            $this->representante_id = null;
        }
    }

    /**
     * Finaliza la vinculación de miembros para préstamos grupales (edición).
     */
    public function finalizarVinculacionGrupo(): void
    {
        $this->normalizeClientesAgregados();

        if (! isset($this->prestamo_id)) {
            $this->addError('prestamo', 'Primero crea el préstamo');

            return;
        }

        if (empty($this->clientesAgregados)) {
            $this->addError('miembros', 'Debe agregar al menos un miembro al grupo antes de finalizar.');

            return;
        }
        $sync = [];
        $total = 0.0;

        foreach ($this->clientesAgregados as $i => $row) {
            $clienteId = $row['cliente_id'] ?? null;
            $monto = $row['monto_solicitado'] ?? null;

            if (! $clienteId) {
                $this->addError('miembros', "Miembro inválido en la fila {$i}");

                return;
            }

            if (! is_numeric($monto) || (float) $monto <= 0) {
                $this->addError('miembros', "El monto de la fila {$i} debe ser un número mayor a 0.");

                return;
            }

            $sync[$clienteId] = ['monto_solicitado' => (float) $monto];
            $total += (float) $monto;
        }

        DB::transaction(function () use ($sync, $total) {
            $prestamo = Prestamo::findOrFail($this->prestamo_id);
            $prestamo->clientes()->sync($sync);
            $prestamo->monto_total = $total;
            if (isset($this->grupo_id)) {
                $prestamo->grupo_id = $this->grupo_id;
            } elseif (! empty($this->grupo_nombre_selected)) {
                $g = Grupo::where('nombre', $this->grupo_nombre_selected)->first();
                if ($g) {
                    $prestamo->grupo_id = $g->id;
                }
            }
            if (! empty($this->representante_id)) {
                $prestamo->representante_id = $this->representante_id;
            }
            $prestamo->save();
        });

        $this->showMessage('success', 'Agregar clientes completado. Préstamo finalizado con monto total: '.number_format($total, 2));
        redirect()->route('prestamos.index');
    }

    public function enviarAComite(): void
    {
        if (! $this->prestamo_id) {
            $this->addError('prestamo', 'No hay préstamo para enviar.');

            return;
        }

        $prestamo = Prestamo::findOrFail($this->prestamo_id);

        if ($this->producto === 'individual') {
            if (! $this->cliente_id) {
                $this->addError('cliente', 'Debe seleccionar un cliente.');

                return;
            }
            // en individual, asignar solicitante y persistir cliente y monto
            $prestamo->representante_id = $this->cliente_id;
            $prestamo->cliente_id = $this->cliente_id;
            if (is_numeric($this->monto) && (float) $this->monto > 0) {
                $prestamo->monto_total = (float) $this->monto;
            }
        } else {
            if (empty($this->clientesAgregados)) {
                $this->addError('miembros', 'Debe agregar al menos un miembro.');

                return;
            }
            if (! $this->representante_id) {
                $this->addError('representante', 'Debe seleccionar un representante del grupo.');

                return;
            }
            $prestamo->representante_id = $this->representante_id;
            // si el monto_total no está definido aún, calcularlo desde los miembros en memoria
            if (! is_numeric($prestamo->monto_total) || (float) $prestamo->monto_total <= 0) {
                $this->normalizeClientesAgregados();
                $total = 0.0;
                foreach ($this->clientesAgregados as $row) {
                    $m = $row['monto_solicitado'] ?? 0;
                    if (is_numeric($m) && (float) $m > 0) {
                        $total += (float) $m;
                    }
                }
                if ($total > 0) {
                    $prestamo->monto_total = $total;
                }
            }
        }

        // Guardar comentarios del comité (usar trim para limpiar espacios, y null si está vacío)
        $comentario = trim($this->comentarios_comite ?? '');
        $prestamo->comentarios_comite = ! empty($comentario) ? $comentario : null;
        $prestamo->estado = 'en_comite';
        $prestamo->save();

        $prestamo->registrarBitacora('en_comite', 'Enviado a comité. '.($prestamo->comentarios_comite ? 'Comentarios: '.$prestamo->comentarios_comite : ''));

        // Recargar para confirmar que se guardó
        $prestamo->refresh();

        $mensaje = 'Préstamo enviado a comité.';
        if (! empty($prestamo->comentarios_comite)) {
            $mensaje .= ' Comentario guardado correctamente.';
        }

        $this->showMessage('success', $mensaje);
        redirect()->route('prestamos.index');
    }

    /**
     * Seleccionar representante del grupo y persistirlo en el préstamo si existe (edición).
     */
    public function selectRepresentante(int $clienteId): void
    {
        $this->normalizeClientesAgregados();
        $inList = collect($this->clientesAgregados)->contains(function ($r) use ($clienteId) {
            return is_array($r) && isset($r['cliente_id']) && (int) $r['cliente_id'] === (int) $clienteId;
        });

        if (! $inList) {
            $this->addError('representante', 'El representante debe ser miembro del grupo.');

            return;
        }

        $this->representante_id = $clienteId;

        if (! empty($this->prestamo_id)) {
            $prestamo = Prestamo::find($this->prestamo_id);
            if ($prestamo) {
                $prestamo->representante_id = $clienteId;
                $prestamo->save();
            }
        }
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
        $this->validate([
            'new_apellido_paterno' => ['required', 'string', 'max:255'],
            'new_apellido_materno' => ['nullable', 'string', 'max:255'],
            'new_nombres' => ['required', 'string', 'max:255'],
            'new_curp' => ['required', 'string', 'max:18'],
            'new_email' => ['nullable', 'email', 'max:255'],
            'new_pais_nacimiento' => ['nullable', 'string', 'max:255'],
            'new_nombre_conyuge' => ['nullable', 'string', 'max:255'],
            'new_calle_numero' => ['required', 'string', 'max:500'],
            'new_referencia_domiciliaria' => ['nullable', 'string', 'max:1000'],
            'new_estado_civil' => ['nullable', 'string', 'max:100'],
            'new_dependientes_economicos' => ['nullable', 'integer', 'min:0'],
            'new_nombre_aval' => ['nullable', 'string', 'max:255'],
            'new_actividad_productiva' => ['nullable', 'string', 'max:255'],
            'new_anios_experiencia' => ['nullable', 'integer', 'min:0'],
            'new_ingreso_mensual' => ['nullable', 'numeric'],
            'new_gasto_mensual_familiar' => ['nullable', 'numeric'],
            'new_credito_solicitado' => ['nullable', 'numeric'],
            'new_estado' => ['nullable', 'string', 'max:255'],
            'new_municipio' => ['nullable', 'string', 'max:255'],
            'new_colonia' => ['nullable', 'string', 'max:255'],
            'new_codigo_postal' => ['nullable', 'string', 'max:20'],
        ]);

        $cliente = Cliente::create([
            'apellido_paterno' => $this->new_apellido_paterno,
            'apellido_materno' => $this->new_apellido_materno,
            'nombres' => $this->new_nombres,
            'curp' => $this->new_curp,
            'email' => $this->new_email,
            'pais_nacimiento' => $this->new_pais_nacimiento,
            'nombre_conyuge' => $this->new_nombre_conyuge,
            'calle_numero' => $this->new_calle_numero,
            'referencia_domiciliaria' => $this->new_referencia_domiciliaria,
            'estado_civil' => $this->new_estado_civil,
            'dependientes_economicos' => $this->new_dependientes_economicos,
            'nombre_aval' => $this->new_nombre_aval,
            'actividad_productiva' => $this->new_actividad_productiva,
            'anios_experiencia' => $this->new_anios_experiencia,
            'ingreso_mensual' => $this->new_ingreso_mensual,
            'gasto_mensual_familiar' => $this->new_gasto_mensual_familiar,
            'credito_solicitado' => $this->new_credito_solicitado,
            'estado' => $this->new_estado,
            'municipio' => $this->new_municipio,
            'colonia' => $this->new_colonia,
            'codigo_postal' => $this->new_codigo_postal,
        ]);

        $this->cliente_id = $cliente->id;
        $this->showNewClienteForm = false;
        $this->new_apellido_paterno = $this->new_apellido_materno = $this->new_nombres = $this->new_curp = null;
        $this->new_email = $this->new_pais_nacimiento = $this->new_nombre_conyuge = null;
        $this->new_calle_numero = $this->new_referencia_domiciliaria = null;
        $this->new_estado_civil = $this->new_dependientes_economicos = null;
        $this->new_nombre_aval = $this->new_actividad_productiva = null;
        $this->new_anios_experiencia = $this->new_ingreso_mensual = $this->new_gasto_mensual_familiar = null;
        $this->new_credito_solicitado = $this->new_estado = $this->new_municipio = $this->new_colonia = $this->new_codigo_postal = null;
        // si estamos en flujo grupal, agregar a la lista de miembros con su monto solicitado
        if ($this->producto === 'grupal') {
            $this->normalizeClientesAgregados();
            $exists = collect($this->clientesAgregados)->first(fn ($r) => is_array($r) && isset($r['cliente_id']) && (int) $r['cliente_id'] === (int) $this->cliente_id);
            if (! $exists) {
                $this->clientesAgregados[] = [
                    'cliente_id' => $this->cliente_id,
                    'monto_solicitado' => (float) ($cliente->credito_solicitado ?? 0),
                    'nombre' => trim("{$cliente->nombres} {$cliente->apellido_paterno}"),
                ];
            }
            if ($this->prestamo_id) {
                $prestamo = Prestamo::find($this->prestamo_id);
                if ($prestamo) {
                    $prestamo->clientes()->syncWithoutDetaching([$cliente->id => ['monto_solicitado' => (float) ($cliente->credito_solicitado ?? 0)]]);
                }
            }
        } elseif ($this->producto === 'individual') {
            // en individual, aplicar el monto del crédito solicitado como monto del préstamo
            $this->monto = (float) ($cliente->credito_solicitado ?? 0);
            $this->cliente_nombre_selected = trim("{$cliente->nombres} {$cliente->apellido_paterno} {$cliente->apellido_materno}");
        }
        $this->showMessage('success', 'Cliente creado y seleccionado');
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
        $this->showMessage('success', 'Grupo creado y seleccionado');
        $this->showClienteModal = true;
    }

    public function openNewGrupoForm(): void
    {
        $this->suggested_grupo_name = $this->generateSuggestedGroupName();
        $this->group_name_suggestions = [
            $this->generateSuggestedGroupName(),
            $this->generateSuggestedGroupName(),
            $this->generateSuggestedGroupName(),
        ];
        $this->showNewGrupoForm = true;
    }

    public function selectSuggestedGroupName(string $name): void
    {
        $this->new_grupo_nombre = $name;
    }

    protected function generateSuggestedGroupName(): string
    {
        $uid = auth()->check() ? auth()->id() : 'anon';
        $time = now()->format('YmdHis');

        return sprintf('GRU-%s-%s-%s', $uid, $time, Str::upper(Str::random(4)));
    }

    // Campos de edición de cliente (modal)
    public $edit_cliente_id = null;

    public $edit_apellido_paterno;

    public $edit_apellido_materno;

    public $edit_nombres;

    public $edit_curp;

    public $edit_email;

    public $edit_pais_nacimiento;

    public $edit_nombre_conyuge;

    public $edit_calle_numero;

    public $edit_referencia_domiciliaria;

    public $edit_estado_civil;

    public $edit_dependientes_economicos;

    public $edit_nombre_aval;

    public $edit_actividad_productiva;

    public $edit_anios_experiencia;

    public $edit_ingreso_mensual;

    public $edit_gasto_mensual_familiar;

    public $edit_credito_solicitado;

    public $edit_estado;

    public $edit_municipio;

    public $edit_colonia;

    public $edit_codigo_postal;

    public function openEditCliente(int $id): void
    {
        $c = Cliente::findOrFail($id);
        $this->edit_cliente_id = $c->id;
        $this->edit_apellido_paterno = $c->apellido_paterno;
        $this->edit_apellido_materno = $c->apellido_materno;
        $this->edit_nombres = $c->nombres;
        $this->edit_curp = $c->curp;
        $this->edit_email = $c->email;
        $this->edit_pais_nacimiento = $c->pais_nacimiento;
        $this->edit_nombre_conyuge = $c->nombre_conyuge;
        $this->edit_calle_numero = $c->calle_numero;
        $this->edit_referencia_domiciliaria = $c->referencia_domiciliaria;
        $this->edit_estado_civil = $c->estado_civil;
        $this->edit_dependientes_economicos = $c->dependientes_economicos;
        $this->edit_nombre_aval = $c->nombre_aval;
        $this->edit_actividad_productiva = $c->actividad_productiva;
        $this->edit_anios_experiencia = $c->anios_experiencia;
        $this->edit_ingreso_mensual = $c->ingreso_mensual;
        $this->edit_gasto_mensual_familiar = $c->gasto_mensual_familiar;
        $this->edit_credito_solicitado = $c->credito_solicitado;
        $this->edit_estado = $c->estado;
        $this->edit_municipio = $c->municipio;
        $this->edit_colonia = $c->colonia;
        $this->edit_codigo_postal = $c->codigo_postal;
        $this->showEditClienteModal = true;
    }

    public function saveEditedCliente(): void
    {
        if (! $this->edit_cliente_id) {
            return;
        }

        $this->validate([
            'edit_apellido_paterno' => ['required', 'string', 'max:255'],
            'edit_apellido_materno' => ['nullable', 'string', 'max:255'],
            'edit_nombres' => ['required', 'string', 'max:255'],
            'edit_curp' => ['required', 'string', 'max:18'],
            'edit_email' => ['nullable', 'email', 'max:255'],
            'edit_pais_nacimiento' => ['nullable', 'string', 'max:255'],
            'edit_nombre_conyuge' => ['nullable', 'string', 'max:255'],
            'edit_calle_numero' => ['required', 'string', 'max:500'],
            'edit_referencia_domiciliaria' => ['nullable', 'string', 'max:1000'],
            'edit_estado_civil' => ['nullable', 'string', 'max:100'],
            'edit_dependientes_economicos' => ['nullable', 'integer', 'min:0'],
            'edit_nombre_aval' => ['nullable', 'string', 'max:255'],
            'edit_actividad_productiva' => ['nullable', 'string', 'max:255'],
            'edit_anios_experiencia' => ['nullable', 'integer', 'min:0'],
            'edit_ingreso_mensual' => ['nullable', 'numeric'],
            'edit_gasto_mensual_familiar' => ['nullable', 'numeric'],
            'edit_credito_solicitado' => ['nullable', 'numeric'],
            'edit_estado' => ['nullable', 'string', 'max:255'],
            'edit_municipio' => ['nullable', 'string', 'max:255'],
            'edit_colonia' => ['nullable', 'string', 'max:255'],
            'edit_codigo_postal' => ['nullable', 'string', 'max:20'],
        ]);

        $c = Cliente::findOrFail($this->edit_cliente_id);
        $c->update([
            'apellido_paterno' => $this->edit_apellido_paterno,
            'apellido_materno' => $this->edit_apellido_materno,
            'nombres' => $this->edit_nombres,
            'curp' => $this->edit_curp,
            'email' => $this->edit_email,
            'pais_nacimiento' => $this->edit_pais_nacimiento,
            'nombre_conyuge' => $this->edit_nombre_conyuge,
            'calle_numero' => $this->edit_calle_numero,
            'referencia_domiciliaria' => $this->edit_referencia_domiciliaria,
            'estado_civil' => $this->edit_estado_civil,
            'dependientes_economicos' => $this->edit_dependientes_economicos,
            'nombre_aval' => $this->edit_nombre_aval,
            'actividad_productiva' => $this->edit_actividad_productiva,
            'anios_experiencia' => $this->edit_anios_experiencia,
            'ingreso_mensual' => $this->edit_ingreso_mensual,
            'gasto_mensual_familiar' => $this->edit_gasto_mensual_familiar,
            'credito_solicitado' => $this->edit_credito_solicitado,
            'estado' => $this->edit_estado,
            'municipio' => $this->edit_municipio,
            'colonia' => $this->edit_colonia,
            'codigo_postal' => $this->edit_codigo_postal,
        ]);

        // Ajustar según producto
        if ($this->producto === 'individual') {
            $this->monto = (float) ($c->credito_solicitado ?? 0);
            $this->cliente_id = $c->id;
            $this->cliente_nombre_selected = trim("{$c->nombres} {$c->apellido_paterno} {$c->apellido_materno}");
        } else {
            // grupal: actualizar o insertar en clientesAgregados
            $this->normalizeClientesAgregados();
            $found = false;
            foreach ($this->clientesAgregados as $i => $row) {
                if ((int) ($row['cliente_id'] ?? 0) === $c->id) {
                    $this->clientesAgregados[$i]['monto_solicitado'] = (float) ($c->credito_solicitado ?? 0);
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $this->clientesAgregados[] = [
                    'cliente_id' => $c->id,
                    'monto_solicitado' => (float) ($c->credito_solicitado ?? 0),
                    'nombre' => trim("{$c->nombres} {$c->apellido_paterno}"),
                ];
            }
            // sincronizar inmediatamente en pivot
            if ($this->prestamo_id) {
                $prestamo = Prestamo::find($this->prestamo_id);
                if ($prestamo) {
                    $prestamo->clientes()->syncWithoutDetaching([$c->id => ['monto_solicitado' => (float) ($c->credito_solicitado ?? 0)]]);
                }
            }
        }

        $this->showEditClienteModal = false;
        $this->showMessage('success', 'Cliente actualizado y aplicado al préstamo');
    }

    protected function normalizeClientesAgregados(): void
    {
        if (! is_array($this->clientesAgregados)) {
            $this->clientesAgregados = [];

            return;
        }

        $first = reset($this->clientesAgregados);
        if (is_array($first) && count($first) === 1 && is_array($first[0]) && isset($first[0][0]) && is_array($first[0][0])) {
            $flat = [];
            foreach ($this->clientesAgregados as $item) {
                if (is_array($item)) {
                    foreach ($item as $sub) {
                        if (is_array($sub)) {
                            foreach ($sub as $row) {
                                if (is_array($row) && isset($row['cliente_id'])) {
                                    $flat[] = $row;
                                }
                            }
                        }
                    }
                }
            }
            if (! empty($flat)) {
                $this->clientesAgregados = $flat;

                return;
            }
        }

        $filtered = array_values(array_filter($this->clientesAgregados, function ($r) {
            return is_array($r) && isset($r['cliente_id']);
        }));
        $this->clientesAgregados = $filtered;
    }

    /**
     * Elimina el cliente seleccionado del préstamo individual
     */
    public function removeCliente(): void
    {
        // Si existe un préstamo y tiene un cliente vinculado, lo desvinculamos
        if ($this->prestamo_id) {
            $prestamo = Prestamo::find($this->prestamo_id);
            if ($prestamo && $prestamo->cliente_id) {
                $prestamo->cliente_id = null;
                $prestamo->save();
            }
        }

        // Limpiamos las variables locales
        $this->cliente_id = null;
        $this->cliente_nombre_selected = null;

        // Mostramos mensaje de confirmación
        $this->showMessage('success', 'Cliente eliminado del préstamo correctamente.');
    }

    /**
     * Buscar asesores basado en el término de búsqueda
     */
    public function searchAsesores(): void
    {
        if (empty($this->asesorSearch)) {
            $this->asesores = User::whereHas('roles', function ($query) {
                $query->where('name', 'Asesor');
            })->limit(10)->get();
        } else {
            $this->asesores = User::whereHas('roles', function ($query) {
                $query->where('name', 'Asesor');
            })
                ->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->asesorSearch.'%')
                        ->orWhere('email', 'like', '%'.$this->asesorSearch.'%');
                })
                ->limit(10)
                ->get();
        }
    }

    /**
     * Seleccionar un asesor del dropdown
     */
    public function selectAsesor(int $asesorId): void
    {
        $asesor = User::find($asesorId);
        if ($asesor) {
            $this->asesor_id = $asesor->id;
            $this->asesorSelected = [
                'id' => $asesor->id,
                'name' => $asesor->name,
                'email' => $asesor->email,
            ];
            $this->asesorSearch = '';
            $this->asesores = [];
        }
    }

    /**
     * Limpiar la selección del asesor
     */
    public function clearAsesor(): void
    {
        $this->asesor_id = null;
        $this->asesorSelected = null;
        $this->asesorSearch = '';
        $this->asesores = [];
    }

    /**
     * Actualización reactiva del campo de búsqueda
     */
    public function updatedAsesorSearch(): void
    {
        if (empty($this->asesorSearch) && ! $this->asesorSelected) {
            $this->asesores = [];
        }
    }
}
