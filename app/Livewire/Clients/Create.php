<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use App\Models\Cliente;

class Create extends Component
{
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
    public $dependientes_economicos = 0;
    public $nombre_aval;
    public $actividad_productiva;
    public $anios_experiencia;
    public $ingreso_mensual = 0;
    public $gasto_mensual_familiar = 0;
    public $credito_solicitado = 0;
    public $estado;
    public $municipio;
    public $colonia;
    public $codigo_postal;
    public $telefono;
    public $phones = [];
    public $phoneToDeleteIndex = null;

    protected function rules(): array
    {
        return [
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'nombres' => ['required', 'string', 'max:255'],
            'curp' => ['required', 'string', 'max:18', 'unique:clientes,curp'],
            'email' => ['nullable', 'email', 'max:255', 'unique:clientes,email'],
            'pais_nacimiento' => ['nullable', 'string', 'max:255'],
            'nombre_conyuge' => ['nullable', 'string', 'max:255'],
            'calle_numero' => ['required', 'string', 'max:500'],
            'referencia_domiciliaria' => ['nullable', 'string', 'max:1000'],
            'estado_civil' => ['required', 'string', 'max:100'],
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
            'telefono' => ['nullable', 'string', 'max:30'],
            'phones' => ['nullable', 'array'],
            'phones.*.numero' => ['nullable', 'string', 'max:30', 'regex:/^[0-9\s()+-]{7,20}$/'],
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
        'max' => 'El campo :attribute no debe ser mayor de :max caracteres.',
        'required' => 'El campo :attribute es obligatorio.',
    ];

    public function save()
    {
        $this->validate();

        $cliente = Cliente::create([
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
            'dependientes_economicos' => $this->dependientes_economicos ?? 0,
            'nombre_aval' => $this->nombre_aval,
            'actividad_productiva' => $this->actividad_productiva,
            'anios_experiencia' => $this->anios_experiencia,
            'ingreso_mensual' => $this->ingreso_mensual ?? 0,
            'gasto_mensual_familiar' => $this->gasto_mensual_familiar ?? 0,
            'credito_solicitado' => $this->credito_solicitado ?? 0,
            'estado' => $this->estado,
            'municipio' => $this->municipio,
            'colonia' => $this->colonia,
            'codigo_postal' => $this->codigo_postal,
        ]);

        // Guardar teléfonos enviados (si existen)
        $phones = $this->phones ?? [];
        foreach ($phones as $p) {
            $numero = data_get($p, 'numero');
            if ($numero) {
                $cliente->telefonos()->create(['tipo' => data_get($p, 'tipo', 'celular'), 'numero' => $numero]);
            }
        }

        session()->flash('success', 'Cliente creado correctamente');

        return redirect()->route('clients.index');
    }

    /**
     * Livewire hook called when $curp is updated from the frontend.
     * Normalize to uppercase and strip non-alphanumeric chars, limit to 18.
     */
    public function updatedCurp($value): void
    {
        $sanitized = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $value));
        $this->curp = substr($sanitized, 0, 18);
    }

    public function updatedEstadoCivil(string $value): void
    {
        // Si la persona es soltera, limpiar el nombre del cónyuge
        if ($value === 'soltero') {
            $this->nombre_conyuge = null;
        }
    }

    public function addPhone(): void
    {
        $this->phones[] = ['tipo' => 'celular', 'numero' => ''];
    }

    public function removePhone(int $index): void
    {
        // pedir confirmación en frontend antes de eliminar
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

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.clients.create');
    }
}
