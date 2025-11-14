<?php

namespace App\Livewire\Settings;

use App\Models\Configuration;
use Livewire\Component;

class Configurations extends Component
{
    public $configurations = [];
    public $editingConfig = null;
    public $editValue = '';
    public $showSuccessMessage = false;

    // Configuraciones por defecto del sistema
    protected $defaultConfigurations = [
        [
            'key' => 'garantia_default',
            'value' => '10.0',
            'type' => 'decimal',
            'description' => 'Porcentaje de garantía por defecto para préstamos',
            'category' => 'financial',
            'editable' => true,
        ],
        [
            'key' => 'tasa_interes_default',
            'value' => '4.5',
            'type' => 'decimal',
            'description' => 'Tasa de interés por defecto (porcentaje mensual)',
            'category' => 'financial',
            'editable' => true,
        ],
        [
            'key' => 'iva_percentage',
            'value' => '16.0',
            'type' => 'decimal',
            'description' => 'Porcentaje de IVA aplicable',
            'category' => 'financial',
            'editable' => true,
        ],
        [
            'key' => 'company_name',
            'value' => 'Diner',
            'type' => 'string',
            'description' => 'Nombre de la empresa',
            'category' => 'general',
            'editable' => true,
        ],
        [
            'key' => 'company_address',
            'value' => 'Motul, Yucatán',
            'type' => 'string',
            'description' => 'Dirección de la empresa',
            'category' => 'general',
            'editable' => true,
        ],
    ];

    public function mount(): void
    {
        $this->initializeDefaultConfigurations();
        $this->loadConfigurations();
    }

    public function initializeDefaultConfigurations(): void
    {
        foreach ($this->defaultConfigurations as $config) {
            Configuration::firstOrCreate(
                ['key' => $config['key']],
                $config
            );
        }
    }

    public function loadConfigurations(): void
    {
        $this->configurations = Configuration::orderBy('category')->orderBy('key')->get()->toArray();
    }

    public function startEditing($configId): void
    {
        $config = collect($this->configurations)->firstWhere('id', $configId);
        $this->editingConfig = $configId;
        $this->editValue = $config['value'];
    }

    public function cancelEditing(): void
    {
        $this->editingConfig = null;
        $this->editValue = '';
    }

    public function saveConfiguration($configId): void
    {
        $config = Configuration::find($configId);

        if (!$config || !$config->editable) {
            $this->addError('general', 'No se puede editar esta configuración.');
            return;
        }

        // Validar según el tipo
        $this->validateValue($config->type);

        $config->update(['value' => $this->editValue]);

        $this->editingConfig = null;
        $this->editValue = '';
        $this->loadConfigurations();

        $this->showSuccessMessage = true;
        $this->dispatch('configurationUpdated');
    }

    protected function validateValue(string $type): void
    {
        switch ($type) {
            case 'decimal':
                $this->validate([
                    'editValue' => 'required|numeric|min:0',
                ], [
                    'editValue.required' => 'El valor es obligatorio.',
                    'editValue.numeric' => 'Debe ser un número válido.',
                    'editValue.min' => 'El valor debe ser mayor a 0.',
                ]);
                break;

            case 'integer':
                $this->validate([
                    'editValue' => 'required|integer|min:0',
                ], [
                    'editValue.required' => 'El valor es obligatorio.',
                    'editValue.integer' => 'Debe ser un número entero.',
                    'editValue.min' => 'El valor debe ser mayor a 0.',
                ]);
                break;

            case 'string':
                $this->validate([
                    'editValue' => 'required|string|max:255',
                ], [
                    'editValue.required' => 'El valor es obligatorio.',
                    'editValue.string' => 'Debe ser texto válido.',
                    'editValue.max' => 'Máximo 255 caracteres.',
                ]);
                break;
        }
    }

    public function render()
    {
        return view('livewire.settings.configurations', [
            'configurations' => $this->configurations,
        ]);
    }
}
