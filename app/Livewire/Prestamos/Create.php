<?php

namespace App\Livewire\Prestamos;

use App\Models\Cliente;
use App\Models\Grupo;
use App\Models\Prestamo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class Create extends Component
{
    public $tipo = 'cliente'; // 'cliente' o 'grupo'

    public $step = 1;

    public $producto = 'individual'; // 'individual' o 'grupal'

    // Status alert properties
    public $status_type = 'success';

    public $status_message = '';

    // Para forzar actualizaciones de la vista
    public $updateCounter = 0;

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

    public $garantia = 10.00;

    public $asesor_id;

    public $asesorSearch = '';

    public $asesorSelected;

    public $asesores = [];

    public $fecha_primer_pago;

    public $comentarios_comite = '';

    // UX helpers
    public $clienteSearch = '';

    public $grupoSearch = '';

    // Modal controls
    public $showClienteModal = false;

    public $showGrupoModal = false;

    public $showEditClienteModal = false;

    // Modal filters (extra)
    public $cliente_filter_curp = '';

    public $cliente_filter_apellido = '';

    // Selected names for badge
    public $cliente_nombre_selected;

    public $grupo_nombre_selected;

    // clientes agregados (solo para grupal)
    public $clientesAgregados = []; // array of ['cliente_id'=>, 'monto_solicitado'=>]

    public $representante_id = null; // cliente designado como representante

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

    public function mount($prestamo = null): void
    {
        $this->isAdmin = auth()->check() && auth()->user()->hasRole('Administrador');

        // Si hay mensajes flash en la sesión, establecerlos en las propiedades de alerta
        if (session()->has('success')) {
            $this->status_type = 'success';
            $this->status_message = session('success');
        } elseif (session()->has('error')) {
            $this->status_type = 'error';
            $this->status_message = session('error');
        }

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
            $this->garantia = $prestamo->garantia ?? $this->garantia;
            $this->comentarios_comite = $prestamo->comentarios_comite ?? $this->comentarios_comite;

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

            // If prestamo exists but clients not linked (or missing), go to step 2 to continue
            $this->step = 2;
        }

        // Cargar todos los asesores disponibles
        $this->asesores = User::whereHas('roles', function ($query) {
            $query->where('name', 'Asesor');
        })->orderBy('name')->get();
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
            // 'cliente_id' => ['required_if:tipo,cliente', 'exists:clientes,id'],
            // 'grupo_id' => ['required_if:tipo,grupo', 'exists:grupos,id'],

            'plazo' => ['required', 'in:4meses,4mesesD,5mesesD,6meses,1ano'],
            'periodicidad' => ['required', 'in:semanal,catorcenal,quincenal'],
            'dia_pago' => ['required', 'in:lunes,martes,miercoles,jueves,viernes'],
            'fecha_entrega' => ['required', 'date'],
            'fecha_primer_pago' => ['required', 'date'],
            'garantia' => ['required', 'numeric', 'min:0', 'max:100'],
            'asesor_id' => ['nullable', 'exists:users,id'],
            'comentarios_comite' => ['nullable', 'string', 'max:1000'],
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
        $fields = ['producto', 'plazo', 'periodicidad', 'fecha_entrega', 'fecha_primer_pago', 'dia_pago', 'garantia', 'asesor_id', 'comentarios_comite'];
        $allRules = method_exists($this, 'rules') ? $this->rules() : (property_exists($this, 'rules') ? $this->rules : []);

        $rulesSubset = [];
        foreach ($fields as $f) {
            if (isset($allRules[$f])) {
                $rulesSubset[$f] = $allRules[$f];
            }
        }

        $validatedData = $this->validate($rulesSubset);

        // La validación del representante se hace solo en métodos de finalización
        // No aquí, para permitir el paso del step 1 al 2

        return $validatedData;
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
        $periodDays = match ($period) {
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
        // Debug: Log del inicio del método
        \Log::debug('crearPrestamo: Iniciando creación', [
            'producto' => $this->producto,
            'plazo' => $this->plazo,
            'periodicidad' => $this->periodicidad,
            'fecha_entrega' => $this->fecha_entrega,
            'fecha_primer_pago' => $this->fecha_primer_pago,
            'asesor_id' => $this->asesor_id,
        ]);

        // Limpiar errores previos
        $this->resetErrorBag();

        // validar primer paso
        try {
            $this->validateFirstStep();
            $this->validateFechaPrimerPago();
            \Log::debug('crearPrestamo: Validación exitosa');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::debug('crearPrestamo: Error de validación', ['errors' => $e->errors()]);
            $this->showMessage('error', 'Por favor corrige los errores en el formulario antes de continuar.');
            return;
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            \Log::debug('crearPrestamo: Errores en errorBag', ['errors' => $this->getErrorBag()->toArray()]);
            $this->showMessage('error', 'Por favor corrige los errores marcados en el formulario.');
            return;
        }

        $data = [
            'producto' => $this->producto,
            'plazo' => $this->plazo,
            'periodicidad' => $this->periodicidad,
            'periodo_pago' => $this->periodo_pago,
            'fecha_entrega' => $this->fecha_entrega,
            'fecha_primer_pago' => $this->fecha_primer_pago,
            'dia_pago' => $this->dia_pago,
            'monto_total' => 0,
            'estado' => 'en_curso',
            'garantia' => $this->garantia,
            'asesor_id' => $this->asesor_id,
            'comentarios_comite' => $this->comentarios_comite,
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

        // Si es grupal, crear grupo automático si no existe aún
        if ($this->producto === 'grupal') {
            // Si no hay grupo seleccionado, creamos uno automático
            if (empty($this->grupo_id)) {
                $grupo = $this->ensureAutoGrupoExists();
                $prestamo->grupo_id = $grupo->id;
                $prestamo->save();
                $this->grupo_id = $grupo->id;
                $this->grupo_nombre_selected = $grupo->nombre;
            }

            // Si ya hay un representante seleccionado, lo asignamos al préstamo
            if (! empty($this->representante_id)) {
                $prestamo->representante_id = $this->representante_id;
                $prestamo->save();
            }
        }

        $this->prestamo_id = $prestamo->id;
        $this->step = 2;
        $this->showMessage('success', 'Préstamo creado con #: '.$prestamo->id);

        \Log::debug('crearPrestamo: Préstamo creado exitosamente', [
            'prestamo_id' => $prestamo->id,
            'step' => $this->step
        ]);
    }

    public function updatePrestamo(): void
    {
        if (! $this->prestamo_id) {
            $this->addError('prestamo', 'No hay préstamo cargado para actualizar.');
            $this->showMessage('error', 'No hay préstamo cargado para actualizar.');

            return;
        }

        $this->validateFirstStep();
        $this->validateFechaPrimerPago();

        if ($this->getErrorBag()->isNotEmpty()) {
            $this->showMessage('error', 'Hay errores en el formulario. Por favor revisa los campos marcados.');

            return;
        }

        $prestamo = Prestamo::findOrFail($this->prestamo_id);

        $prestamo->producto = $this->producto;
        $prestamo->plazo = $this->plazo;
        $prestamo->periodicidad = $this->periodicidad;
        $prestamo->periodo_pago = $this->periodo_pago;
        $prestamo->fecha_entrega = $this->fecha_entrega;
        $prestamo->fecha_primer_pago = $this->fecha_primer_pago;
        $prestamo->dia_pago = $this->dia_pago;
        $prestamo->garantia = $this->garantia;
        $prestamo->asesor_id = $this->asesor_id;
        $prestamo->comentarios_comite = $this->comentarios_comite;

        // Solo permitir cambiar tasa_interes si el usuario es administrador
        if (auth()->check() && auth()->user()->hasRole('Administrador')) {
            $prestamo->tasa_interes = $this->tasa_interes;
        }

        // Si es grupal, solo asignar representante si ya está seleccionado
        // No validamos que sea obligatorio aquí porque aún estamos en proceso de agregar clientes
        if ($prestamo->producto === 'grupal') {
            // Si hay un representante seleccionado, lo asignamos al préstamo
            if (! empty($this->representante_id)) {
                $prestamo->representante_id = $this->representante_id;
            }
        }

        $prestamo->save();

        // Cambiar al paso 2 para agregar clientes
        $this->step = 2;

        // Llamamos al método específico para mostrar mensajes
        $this->showMessage('success', 'Préstamo actualizado correctamente. Ahora puedes agregar clientes.');
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
            'descripcion' => 'Grupo autogenerado al crear el préstamo',
        ]);
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

        // dispatch browser event / Livewire dispatch helper for frontend feedback
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
        // abrir modal de edición para corroborar datos y capturar monto
        if ($cliente) {
            $this->openEditCliente($cliente->id);
        }

        // if a group is selected, add the client to the clientesAgregados list (avoid duplicates)
        if ($this->grupo_id) {
            // normalize existing structure first
            $this->normalizeClientesAgregados();
            $exists = collect($this->clientesAgregados)->first(fn ($r) => is_array($r) && isset($r['cliente_id']) && $r['cliente_id'] == $cliente->id);
            if (! $exists) {
                $this->clientesAgregados[] = ['cliente_id' => $cliente->id, 'monto_solicitado' => null, 'nombre' => trim("{$cliente->nombres} {$cliente->apellido_paterno}")];
            }
            // persistir en pivot inmediatamente si ya existe el préstamo
            if ($this->prestamo_id && $cliente) {
                $prestamo = Prestamo::find($this->prestamo_id);
                if ($prestamo) {
                    $prestamo->clientes()->syncWithoutDetaching([$cliente->id => ['monto_solicitado' => 0]]);
                }
            }
        }
    }

    /**
     * Guardar miembro por índice: leer monto desde clientesAgregados y llamar a agregarClienteAlGrupo
     */
    public function guardarMiembro(int $index): void
    {
        // normalizar la estructura antes de operar (evita arrays anidados que vienen del serializador)
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
     * Finaliza la vinculación de miembros para préstamos grupales.
     * Valida montos, asegura que todos los miembros estén persistidos y suma monto_total.
     */
    public function finalizarVinculacionGrupo(): void
    {
        // asegurarnos que la estructura esté normalizada
        $this->normalizeClientesAgregados();

        if (! isset($this->prestamo_id)) {
            $this->addError('prestamo', 'Primero crea el préstamo');

            return;
        }

        if (empty($this->clientesAgregados)) {
            $this->addError('miembros', 'Debe agregar al menos un miembro al grupo antes de finalizar.');

            return;
        }

        // build sync payload and calculate total in memory
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

        // persistir todos los miembros en una transacción y actualizar monto_total
        DB::transaction(function () use ($sync, $total) {
            $prestamo = Prestamo::findOrFail($this->prestamo_id);
            // reemplazar set completo de clientes para evitar inconsistencias
            $prestamo->clientes()->sync($sync);
            $prestamo->monto_total = $total;
            // si hay un grupo seleccionado en el componente, persistirlo en el préstamo
            if (isset($this->grupo_id)) {
                $prestamo->grupo_id = $this->grupo_id;
            } elseif (! empty($this->grupo_nombre_selected)) {
                // intentar resolver grupo por nombre si el componente tiene el nombre seleccionado
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

        session()->flash('success', 'Vinculación completada. Préstamo finalizado con monto total: '.number_format($total, 2));
        // redirect to index
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
            // en individual, el solicitante es el mismo cliente
            $prestamo->representante_id = $this->cliente_id;
            // también persistimos el cliente asociado y el monto si está definido en el componente
            $prestamo->cliente_id = $this->cliente_id;
            if (is_numeric($this->monto) && (float) $this->monto > 0) {
                $prestamo->monto_total = (float) $this->monto;
            }
        } else {
            // grupal: debe haber al menos un miembro y un representante elegido
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

        // Recargar para confirmar que se guardó
        $prestamo->refresh();

        $mensaje = 'Préstamo enviado a comité.';
        if (! empty($prestamo->comentarios_comite)) {
            $mensaje .= ' Comentario guardado correctamente.';
        }

        session()->flash('success', $mensaje);
        redirect()->route('prestamos.index');
    }

    /**
     * Seleccionar representante del grupo y persistirlo en el préstamo si existe.
     */
    public function selectRepresentante(int $clienteId): void
    {
        // asegurar que el cliente esté en la lista de agregados
        $this->normalizeClientesAgregados();
        $inList = collect($this->clientesAgregados)->contains(function ($r) use ($clienteId) {
            return is_array($r) && isset($r['cliente_id']) && (int) $r['cliente_id'] === (int) $clienteId;
        });

        if (! $inList) {
            $this->addError('representante', 'El representante debe ser miembro del grupo.');

            return;
        }

        $this->representante_id = $clienteId;

        // si hay préstamo creado, persistir inmediatamente
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
        // cargar miembros del grupo para poder agregarlos
        if ($grupo) {
            $this->clientesAgregados = $grupo->clientes()->get()->map(function ($c) {
                return ['cliente_id' => $c->id, 'monto_solicitado' => null, 'nombre' => trim("{$c->nombres} {$c->apellido_paterno}")];
            })->toArray();
        }
        // persistir selección de grupo en el préstamo si ya existe
        if (! empty($this->prestamo_id) && ! empty($this->grupo_id)) {
            $prestamo = Prestamo::find($this->prestamo_id);
            if ($prestamo) {
                $prestamo->grupo_id = $this->grupo_id;
                $prestamo->save();
            }
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

        // Cerrar el modal
        $this->showNewClienteForm = false;

        // Limpiar campos del formulario
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
        session()->flash('success', 'Cliente creado y seleccionado');
    }

    public function addNewGrupo()
    {
        // if user didn't provide a name, generate a suggested unique one
        if (empty($this->new_grupo_nombre)) {
            $this->new_grupo_nombre = $this->generateSuggestedGroupName();
        }

        $data = $this->validate([
            'new_grupo_nombre' => ['required', 'string', 'max:255', 'unique:grupos,nombre'],
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
        // abrir modal de clientes para agregar miembros inmediatamente
        $this->showClienteModal = true;
        // si ya existe un préstamo en curso, persistir el grupo en el préstamo
        if (! empty($this->prestamo_id)) {
            $prestamo = Prestamo::find($this->prestamo_id);
            if ($prestamo) {
                $prestamo->grupo_id = $this->grupo_id;
                $prestamo->save();
            }
        }
    }

    /**
     * Abrir el formulario de nuevo grupo con un nombre sugerido
     */
    public function openNewGrupoForm(): void
    {
        $this->suggested_grupo_name = $this->generateSuggestedGroupName();
        // generar 3 sugerencias
        $this->group_name_suggestions = [
            $this->generateSuggestedGroupName(),
            $this->generateSuggestedGroupName(),
            $this->generateSuggestedGroupName(),
        ];
        // do not overwrite the input value, show suggestion instead
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

        // Ajustar monto o lista según producto
        if ($this->producto === 'individual') {
            $this->monto = (float) ($c->credito_solicitado ?? 0);
            $this->cliente_id = $c->id;
            $this->cliente_nombre_selected = trim("{$c->nombres} {$c->apellido_paterno} {$c->apellido_materno}");
        } else {
            // grupal: si ya está en la lista, actualizar su monto; si no, agregarlo
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
            // si ya existe el préstamo en BD y el cliente está vinculado, sincronizar pivot inmediatamente
            if ($this->prestamo_id) {
                $prestamo = Prestamo::find($this->prestamo_id);
                if ($prestamo) {
                    $prestamo->clientes()->syncWithoutDetaching([$c->id => ['monto_solicitado' => (float) ($c->credito_solicitado ?? 0)]]);
                }
            }
        }

        $this->showEditClienteModal = false;
        session()->flash('success', 'Cliente actualizado y aplicado al préstamo');
    }

    /**
     * Normaliza $this->clientesAgregados eliminando niveles inesperados
     * que a veces aparecen por la serialización de Livewire.
     */

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

        // remover elemento y reindexar
        unset($this->clientesAgregados[$index]);
        $this->clientesAgregados = array_values($this->clientesAgregados);

        // si era representante, limpiar selección
        if (isset($row['cliente_id']) && (int) $this->representante_id === (int) $row['cliente_id']) {
            $this->representante_id = null;
        }
    }

    protected function normalizeClientesAgregados(): void
    {
        if (! is_array($this->clientesAgregados)) {
            $this->clientesAgregados = [];

            return;
        }

        // aplanar un nivel si el primer elemento es un array que contiene arrays
        $first = reset($this->clientesAgregados);
        if (is_array($first) && count($first) === 1 && is_array($first[0]) && isset($first[0][0]) && is_array($first[0][0])) {
            // detectamos estructura [[[{...},{...}]], {...}] -> queremos [{...},{...}]
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

        // fallback: filtrar sólo elementos que parecen filas válidas
        $filtered = array_values(array_filter($this->clientesAgregados, function ($r) {
            return is_array($r) && isset($r['cliente_id']);
        }));
        $this->clientesAgregados = $filtered;
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

        $prestamo = new Prestamo;
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

    /**
     * Cuando cambia el asesor_id, actualizar asesorSelected para compatibilidad
     */
    public function updatedAsesorId(): void
    {
        if ($this->asesor_id) {
            $asesor = $this->asesores->firstWhere('id', $this->asesor_id);
            if ($asesor) {
                $this->asesorSelected = [
                    'id' => $asesor->id,
                    'name' => $asesor->name,
                    'email' => $asesor->email,
                ];
            }
        } else {
            $this->asesorSelected = null;
        }
    }
}
