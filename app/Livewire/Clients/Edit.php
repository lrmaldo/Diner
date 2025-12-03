<?php

namespace App\Livewire\Clients;

use App\Models\Cliente;
use Livewire\Component;

class Edit extends Component
{
    public Cliente $cliente;

    public $phones = [];

    public $phoneToDeleteIndex = null;

    // Campos del cliente (propiedades públicas para enlazado seguro en Livewire)
    public $apellido_paterno;

    public $apellido_materno;

    public $nombres;

    public $curp;

    public $email;

    public $pais_nacimiento;

    public $nombre_conyuge;

    public $calle_numero;

    public $referencia_domiciliaria;

    public $estado_civil;

    public $dependientes_economicos;

    public $nombre_aval;

    public $actividad_productiva;

    public $anios_experiencia;

    public $ingreso_mensual;

    public $gasto_mensual_familiar;

    public $credito_solicitado;

    public $estado;

    public $municipio;

    public $colonia;

    public $codigo_postal;

    protected function rules(): array
    {
        return [
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'nombres' => ['required', 'string', 'max:255'],
            'curp' => ['required', 'string', 'max:18', "unique:clientes,curp,{$this->cliente->id}"],
            'email' => ['nullable', 'email', 'max:255', "unique:clientes,email,{$this->cliente->id}"],
            'pais_nacimiento' => ['nullable', 'string', 'max:255'],
            'nombre_conyuge' => ['nullable', 'string', 'max:255'],
            'calle_numero' => ['required', 'string', 'max:500'],
            'referencia_domiciliaria' => ['nullable', 'string', 'max:1000'],
            'estado_civil' => ['nullable', 'string', 'max:100'],
            'dependientes_economicos' => ['nullable', 'integer', 'min:0'],
            'nombre_aval' => ['nullable', 'string', 'max:255'],
            'actividad_productiva' => ['nullable', 'string', 'max:255'],
            'anios_experiencia' => ['nullable', 'integer', 'min:0'],
            'ingreso_mensual' => ['nullable', 'numeric'],
            'gasto_mensual_familiar' => ['nullable', 'numeric'],
            'credito_solicitado' => ['nullable', 'numeric'],
            'estado' => ['nullable', 'string', 'max:255'],
            'municipio' => ['nullable', 'string', 'max:255'],
            'colonia' => ['nullable', 'string', 'max:255'],
            'codigo_postal' => ['nullable', 'string', 'max:20'],
            'phones.*.numero' => ['nullable', 'string', 'max:30', 'regex:/^[0-9\\s()+-]{7,20}$/'],
        ];
    }

    protected $messages = [
        'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
        'nombres.required' => 'El nombre es obligatorio.',
        'curp.required' => 'La CURP es obligatoria.',
        'curp.unique' => 'La CURP ya está registrada.',
        'email.email' => 'El correo electrónico no tiene un formato válido.',
        'email.unique' => 'El correo electrónico ya está registrado.',
        'calle_numero.required' => 'La calle y número son obligatorios.',
        'dependientes_economicos.integer' => 'Los dependientes económicos deben ser un número entero.',
        'anios_experiencia.integer' => 'Los años de experiencia deben ser un número entero.',
        'ingreso_mensual.numeric' => 'El ingreso mensual debe ser un número.',
        'gasto_mensual_familiar.numeric' => 'El gasto mensual debe ser un número.',
        'credito_solicitado.numeric' => 'El crédito solicitado debe ser un número.',
        'phones.*.numero.regex' => 'El número de teléfono tiene un formato inválido.',
        'max' => 'El campo :attribute no debe ser mayor de :max caracteres.',
        'required' => 'El campo :attribute es obligatorio.',
    ];

    public function mount(Cliente $cliente)
    {
        $this->cliente = $cliente;
        // Inicializar propiedades simples desde el modelo
        $this->apellido_paterno = $cliente->apellido_paterno;
        $this->apellido_materno = $cliente->apellido_materno;
        $this->nombres = $cliente->nombres;
        $this->curp = $cliente->curp;
        $this->email = $cliente->email;
        $this->pais_nacimiento = $cliente->pais_nacimiento;
        $this->nombre_conyuge = $cliente->nombre_conyuge;
        $this->calle_numero = $cliente->calle_numero;
        $this->referencia_domiciliaria = $cliente->referencia_domiciliaria;
        $this->estado_civil = $cliente->estado_civil;
        $this->dependientes_economicos = $cliente->dependientes_economicos;
        $this->nombre_aval = $cliente->nombre_aval;
        $this->actividad_productiva = $cliente->actividad_productiva;
        $this->anios_experiencia = $cliente->anios_experiencia;
        $this->ingreso_mensual = $cliente->ingreso_mensual;
        $this->gasto_mensual_familiar = $cliente->gasto_mensual_familiar;
        $this->credito_solicitado = $cliente->credito_solicitado;
        $this->estado = $cliente->estado;
        $this->municipio = $cliente->municipio;
        $this->colonia = $cliente->colonia;
        $this->codigo_postal = $cliente->codigo_postal;

        $this->phones = $cliente->telefonos->map(function ($t) {
            return ['id' => $t->id, 'tipo' => $t->tipo, 'numero' => $t->numero];
        })->toArray();
    }

    public function addPhone(): void
    {
        $this->phones[] = ['id' => null, 'tipo' => 'celular', 'numero' => ''];
    }

    public function removePhone(int $index): void
    {
        $this->phoneToDeleteIndex = $index;
    }

    public function confirmRemovePhone(): void
    {
        if ($this->phoneToDeleteIndex !== null && isset($this->phones[$this->phoneToDeleteIndex])) {
            array_splice($this->phones, $this->phoneToDeleteIndex, 1);
        }
        $this->phoneToDeleteIndex = null;
    }

    public function cancelRemovePhone(): void
    {
        $this->phoneToDeleteIndex = null;
    }

    public function save()
    {
        $this->validate();

        // Actualizar atributos del cliente desde las propiedades públicas
        $this->cliente->update([
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno,
            'nombres' => $this->nombres,
            'curp' => $this->curp,
            'email' => $this->email,
            'pais_nacimiento' => $this->pais_nacimiento,
            'nombre_conyuge' => $this->nombre_conyuge,
            'calle_numero' => $this->calle_numero,
            'referencia_domiciliaria' => $this->referencia_domiciliaria,
            'estado_civil' => $this->estado_civil,
            'dependientes_economicos' => $this->dependientes_economicos,
            'nombre_aval' => $this->nombre_aval,
            'actividad_productiva' => $this->actividad_productiva,
            'anios_experiencia' => $this->anios_experiencia,
            'ingreso_mensual' => $this->ingreso_mensual,
            'gasto_mensual_familiar' => $this->gasto_mensual_familiar,
            'credito_solicitado' => $this->credito_solicitado,
            'estado' => $this->estado,
            'municipio' => $this->municipio,
            'colonia' => $this->colonia,
            'codigo_postal' => $this->codigo_postal,
        ]);

        // Sincronizar teléfonos: actualizar existentes, crear nuevos, borrar los que faltan
        $existing = $this->cliente->telefonos()->pluck('id')->toArray();
        $sentIds = [];
        foreach ($this->phones as $p) {
            $id = data_get($p, 'id');
            $numero = data_get($p, 'numero');
            $tipo = data_get($p, 'tipo', 'celular');
            if ($id && in_array($id, $existing, true)) {
                // actualizar o borrar si vacío
                if ($numero) {
                    $this->cliente->telefonos()->where('id', $id)->update(['numero' => $numero, 'tipo' => $tipo]);
                    $sentIds[] = $id;
                } else {
                    $this->cliente->telefonos()->where('id', $id)->delete();
                }
            } else {
                // nuevo
                if ($numero) {
                    $created = $this->cliente->telefonos()->create(['tipo' => $tipo, 'numero' => $numero]);
                    $sentIds[] = $created->id;
                }
            }
        }
        // Borrar los telefonos que no fueron enviados
        $toDelete = array_diff($existing, $sentIds);
        if (! empty($toDelete)) {
            $this->cliente->telefonos()->whereIn('id', $toDelete)->delete();
        }

        session()->flash('success', 'Cliente actualizado correctamente');

        return redirect()->route('clients.index');
    }

    /**
     * Normalize CURP when updated from frontend: uppercase, strip non-alphanum, max 18
     */
    public function updatedCurp($value): void
    {
        $sanitized = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $value));
        $this->curp = substr($sanitized, 0, 18);
    }

    public function updatedEstadoCivil($value): void
    {
        if ($value === 'soltero') {
            $this->nombre_conyuge = null;
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.clients.edit', [
            'cliente' => $this->cliente,
        ]);
    }
}
