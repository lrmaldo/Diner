<?php

namespace App\Livewire\Settings;

use App\Models\Holiday;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Holidays extends Component
{
    use WithPagination;

    public string $name = '';
    public string $date = '';
    public string $description = '';
    public string $type = 'national';
    public bool $isRecurring = false;
    public bool $isActive = true;

    public ?Holiday $editingHoliday = null;
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showSuccessMessage = false;

    public string $filterYear = '';
    public string $filterType = '';
    public bool $showActiveOnly = true;

    protected array $rules = [
        'name' => 'required|string|max:255',
        'date' => 'required|date',
        'description' => 'nullable|string|max:500',
        'type' => 'required|in:national,local,religious,company',
        'isRecurring' => 'boolean',
        'isActive' => 'boolean',
    ];

    protected array $messages = [
        'name.required' => 'El nombre del día festivo es obligatorio.',
        'date.required' => 'La fecha es obligatoria.',
        'date.date' => 'Debe ser una fecha válida.',
        'type.required' => 'Debe seleccionar un tipo.',
        'type.in' => 'El tipo seleccionado no es válido.',
    ];

    public function mount(): void
    {
        $this->filterYear = now()->year;
    }

    public function render()
    {
        $query = Holiday::query()
            ->when($this->filterYear, function ($query) {
                $query->whereYear('date', $this->filterYear);
            })
            ->when($this->filterType, function ($query) {
                $query->where('type', $this->filterType);
            })
            ->when($this->showActiveOnly, function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('date');

        $holidays = $query->paginate(15);

        $years = Holiday::selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('livewire.settings.holidays', [
            'holidays' => $holidays,
            'years' => $years,
            'typeOptions' => [
                'national' => 'Nacional',
                'local' => 'Local',
                'religious' => 'Religioso',
                'company' => 'Empresa',
            ],
        ]);
    }

    public function openCreateModal(): void
    {
        $this->reset(['name', 'date', 'description', 'type', 'isRecurring', 'isActive']);
        $this->type = 'national';
        $this->isActive = true;
        $this->showCreateModal = true;
        $this->resetValidation();
    }

    public function hideCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function showEditModal(Holiday $holiday): void
    {
        $this->editingHoliday = $holiday;
        $this->showEditModal = true;
        $this->name = $holiday->name;
        $this->date = $holiday->date->format('Y-m-d');
        $this->description = $holiday->description ?? '';
        $this->type = $holiday->type;
        $this->isRecurring = $holiday->is_recurring;
        $this->isActive = $holiday->is_active;
    }

    public function hideEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingHoliday = null;
        $this->resetForm();
    }

    public function create(): void
    {
        $this->validate();

        $date = Carbon::parse($this->date);

        Holiday::create([
            'name' => $this->name,
            'date' => $date,
            'year' => $date->year,
            'description' => $this->description,
            'type' => $this->type,
            'is_recurring' => $this->isRecurring,
            'is_active' => $this->isActive,
        ]);

        $this->showSuccessMessage = true;
        $this->hideCreateModal();
        $this->resetPage();

        // Auto-hide success message after 3 seconds
        $this->js('setTimeout(() => { $wire.set("showSuccessMessage", false) }, 3000)');
    }

    public function edit(Holiday $holiday): void
    {
        $this->showEditModal($holiday);
    }

    public function update(): void
    {
        $this->validate();

        $date = Carbon::parse($this->date);

        $this->editingHoliday->update([
            'name' => $this->name,
            'date' => $date,
            'year' => $date->year,
            'description' => $this->description,
            'type' => $this->type,
            'is_recurring' => $this->isRecurring,
            'is_active' => $this->isActive,
        ]);

        $this->showSuccessMessage = true;
        $this->hideEditModal();

        // Auto-hide success message after 3 seconds
        $this->js('setTimeout(() => { $wire.set("showSuccessMessage", false) }, 3000)');
    }

    public function cancelEdit(): void
    {
        $this->hideEditModal();
    }

    public function delete(Holiday $holiday): void
    {
        $holiday->delete();
        $this->showSuccessMessage = true;
        $this->resetPage();

        // Auto-hide success message after 3 seconds
        $this->js('setTimeout(() => { $wire.set("showSuccessMessage", false) }, 3000)');
    }

    public function toggleActive(Holiday $holiday): void
    {
        $holiday->update(['is_active' => !$holiday->is_active]);
        $this->showSuccessMessage = true;

        // Auto-hide success message after 3 seconds
        $this->js('setTimeout(() => { $wire.set("showSuccessMessage", false) }, 3000)');
    }

    public function createRecurringForCurrentYear(): void
    {
        Holiday::createRecurringForYear((int) $this->filterYear);
        $this->showSuccessMessage = true;
        $this->resetPage();

        // Auto-hide success message after 3 seconds
        $this->js('setTimeout(() => { $wire.set("showSuccessMessage", false) }, 3000)');
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->date = '';
        $this->description = '';
        $this->type = 'national';
        $this->isRecurring = false;
        $this->isActive = true;
        $this->resetValidation();
    }

    public function updatedFilterYear(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedShowActiveOnly(): void
    {
        $this->resetPage();
    }
}
