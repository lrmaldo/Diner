<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use App\Models\Cliente;

class Edit extends Component
{
    public Cliente $cliente;

    public $phones = [];
    public $phoneToDeleteIndex = null;

    protected function rules(): array
    {
        return [
            'cliente.apellido_paterno' => ['required', 'string', 'max:255'],
            'cliente.apellido_materno' => ['nullable', 'string', 'max:255'],
            'cliente.nombres' => ['required', 'string', 'max:255'],
            'cliente.curp' => ['required', 'string', 'max:18', "unique:clientes,curp,{$this->cliente->id}"],
            'cliente.email' => ['nullable', 'email', 'max:255', "unique:clientes,email,{$this->cliente->id}"],
            'cliente.pais_nacimiento' => ['nullable', 'string', 'max:255'],
            'cliente.nombre_conyuge' => ['nullable', 'string', 'max:255'],
            'cliente.calle_numero' => ['required', 'string', 'max:500'],
            'cliente.referencia_domiciliaria' => ['nullable', 'string', 'max:1000'],
            'cliente.estado_civil' => ['nullable', 'string', 'max:100'],
            'cliente.dependientes_economicos' => ['nullable', 'integer', 'min:0'],
            'cliente.nombre_aval' => ['nullable', 'string', 'max:255'],
            'cliente.actividad_productiva' => ['nullable', 'string', 'max:255'],
            'cliente.anios_experiencia' => ['nullable', 'integer', 'min:0'],
            'cliente.ingreso_mensual' => ['nullable', 'numeric'],
            'cliente.gasto_mensual_familiar' => ['nullable', 'numeric'],
            'cliente.credito_solicitado' => ['nullable', 'numeric'],
            'cliente.estado' => ['nullable', 'string', 'max:255'],
            'cliente.municipio' => ['nullable', 'string', 'max:255'],
            'cliente.colonia' => ['nullable', 'string', 'max:255'],
            'cliente.codigo_postal' => ['nullable', 'string', 'max:20'],
            'phones.*.numero' => ['nullable', 'string', 'max:30', 'regex:/^[0-9\\s()+-]{7,20}$/'],
        ];
    }

    protected $messages = [
        'cliente.apellido_paterno.required' => 'El apellido paterno es obligatorio.',
        'cliente.nombres.required' => 'El nombre es obligatorio.',
        'cliente.curp.required' => 'La CURP es obligatoria.',
        'cliente.curp.unique' => 'La CURP ya está registrada.',
        'cliente.email.email' => 'El correo electrónico no tiene un formato válido.',
        'cliente.email.unique' => 'El correo electrónico ya está registrado.',
        'cliente.calle_numero.required' => 'La calle y número son obligatorios.',
        'cliente.dependientes_economicos.integer' => 'Los dependientes económicos deben ser un número entero.',
        'cliente.anios_experiencia.integer' => 'Los años de experiencia deben ser un número entero.',
        'cliente.ingreso_mensual.numeric' => 'El ingreso mensual debe ser un número.',
        'cliente.gasto_mensual_familiar.numeric' => 'El gasto mensual debe ser un número.',
        'cliente.credito_solicitado.numeric' => 'El crédito solicitado debe ser un número.',
        'phones.*.numero.regex' => 'El número de teléfono tiene un formato inválido.',
        'max' => 'El campo :attribute no debe ser mayor de :max caracteres.',
        'required' => 'El campo :attribute es obligatorio.',
    ];

    public function mount(Cliente $cliente)
    {
        $this->cliente = $cliente;
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

        $this->cliente->save();

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
        if (!empty($toDelete)) {
            $this->cliente->telefonos()->whereIn('id', $toDelete)->delete();
        }

        session()->flash('success', 'Cliente actualizado correctamente');

        return redirect()->route('clients.index');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.clients.edit', [
            'cliente' => $this->cliente,
        ]);
    }
}
