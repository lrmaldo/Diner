<div class="p-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Gestión de Días Festivos</h2>
            <p class="mt-1 text-sm text-gray-600">Administra los días festivos para el cálculo del calendario de pagos</p>
        </div>

        <div class="flex items-center space-x-3">
            <button
                wire:click="createRecurringForCurrentYear"
                class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700"
            >
                Crear Festivos {{ $filterYear }}
            </button>
            <button
                type="button"
                wire:click.prevent="openCreateModal"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Agregar Festivo
            </button>
        </div>
    </div>

    @if($showSuccessMessage)
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
            Operación completada correctamente.
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Modal para Crear Festivo -->
    @if($showCreateModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="hideCreateModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            Nuevo Día Festivo
                        </h3>
                        <button
                            wire:click="hideCreateModal"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="create">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="create-name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre del Festivo *
                                </label>
                                <input
                                    type="text"
                                    id="create-name"
                                    wire:model="name"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    placeholder="Ej: Día de la Independencia"
                                >
                                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="create-date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha *
                                </label>
                                <input
                                    type="date"
                                    id="create-date"
                                    wire:model="date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                                >
                                @error('date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="create-type" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tipo *
                                </label>
                                <select
                                    id="create-type"
                                    wire:model="type"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                                >
                                    @foreach($typeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex items-center space-x-6 pt-6">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        wire:model="isRecurring"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    >
                                    <span class="ml-2 text-sm text-gray-700">Recurrente anual</span>
                                </label>

                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        wire:model="isActive"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    >
                                    <span class="ml-2 text-sm text-gray-700">Activo</span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="create-description" class="block text-sm font-medium text-gray-700 mb-1">
                                Descripción
                            </label>
                            <textarea
                                id="create-description"
                                wire:model="description"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Descripción opcional del día festivo..."
                            ></textarea>
                            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                            <button
                                type="button"
                                wire:click="hideCreateModal"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                            >
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal para Editar Festivo -->
    @if($showEditModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="hideEditModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            Editar Día Festivo
                        </h3>
                        <button
                            wire:click="hideEditModal"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="update">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="edit-name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre del Festivo *
                                </label>
                                <input
                                    type="text"
                                    id="edit-name"
                                    wire:model="name"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    placeholder="Ej: Día de la Independencia"
                                >
                                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="edit-date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha *
                                </label>
                                <input
                                    type="date"
                                    id="edit-date"
                                    wire:model="date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                                >
                                @error('date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="edit-type" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tipo *
                                </label>
                                <select
                                    id="edit-type"
                                    wire:model="type"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                                >
                                    @foreach($typeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex items-center space-x-6 pt-6">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        wire:model="isRecurring"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    >
                                    <span class="ml-2 text-sm text-gray-700">Recurrente anual</span>
                                </label>

                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        wire:model="isActive"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    >
                                    <span class="ml-2 text-sm text-gray-700">Activo</span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="edit-description" class="block text-sm font-medium text-gray-700 mb-1">
                                Descripción
                            </label>
                            <textarea
                                id="edit-description"
                                wire:model="description"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Descripción opcional del día festivo..."
                            ></textarea>
                            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                            <button
                                type="button"
                                wire:click="hideEditModal"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                            >
                                Actualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Formulario de Creación/Edición (ELIMINADO - Reemplazado por modal) -->
    @if(false)
        <div class="mb-6 bg-white shadow-sm rounded-lg p-6 border">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ $editingHoliday ? 'Editar Día Festivo' : 'Nuevo Día Festivo' }}
                </h3>
                <button
                    wire:click="{{ $editingHoliday ? 'cancelEdit' : 'hideCreateForm' }}"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form wire:submit="{{ $editingHoliday ? 'update' : 'create' }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre del Festivo *
                        </label>
                        <input
                            type="text"
                            id="name"
                            wire:model="name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Ej: Día de la Independencia"
                        >
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha *
                        </label>
                        <input
                            type="date"
                            id="date"
                            wire:model="date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                        @error('date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                            Tipo *
                        </label>
                        <select
                            id="type"
                            wire:model="type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                            @foreach($typeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center space-x-6 pt-6">
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                wire:model="isRecurring"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                            <span class="ml-2 text-sm text-gray-700">Recurrente anual</span>
                        </label>

                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                wire:model="isActive"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                            <span class="ml-2 text-sm text-gray-700">Activo</span>
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        Descripción
                    </label>
                    <textarea
                        id="description"
                        wire:model="description"
                        rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                        placeholder="Descripción opcional del día festivo..."
                    ></textarea>
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-end space-x-3">
                    <button
                        type="button"
                        wire:click="{{ $editingHoliday ? 'cancelEdit' : 'hideCreateForm' }}"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        {{ $editingHoliday ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Filtros -->
    <div class="mb-6 bg-gray-50 p-4 rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                <select wire:model.live="filterYear" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">Todos los años</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                    @if(!$years->contains(now()->year))
                        <option value="{{ now()->year }}">{{ now()->year }}</option>
                    @endif
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select wire:model.live="filterType" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">Todos los tipos</option>
                    @foreach($typeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center pt-6">
                <label class="flex items-center">
                    <input
                        type="checkbox"
                        wire:model.live="showActiveOnly"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    >
                    <span class="ml-2 text-sm text-gray-700">Solo activos</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Lista de Festivos -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        @if($holidays->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nombre
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tipo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Recurrente
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($holidays as $holiday)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $holiday->name }}</div>
                                        @if($holiday->description)
                                            <div class="text-sm text-gray-500">{{ $holiday->description }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $holiday->date->format('d/m/Y') }}
                                    <span class="text-gray-500">({{ $holiday->date->translatedFormat('l') }})</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $holiday->type === 'national' ? 'bg-blue-100 text-blue-800' :
                                           ($holiday->type === 'religious' ? 'bg-purple-100 text-purple-800' :
                                           ($holiday->type === 'local' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ $typeOptions[$holiday->type] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $holiday->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $holiday->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($holiday->is_recurring)
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button
                                            wire:click="toggleActive({{ $holiday->id }})"
                                            class="text-{{ $holiday->is_active ? 'red' : 'green' }}-600 hover:text-{{ $holiday->is_active ? 'red' : 'green' }}-900"
                                            title="{{ $holiday->is_active ? 'Desactivar' : 'Activar' }}"
                                        >
                                            @if($holiday->is_active)
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636" />
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @endif
                                        </button>

                                        <button
                                            wire:click="edit({{ $holiday->id }})"
                                            class="text-blue-600 hover:text-blue-900"
                                            title="Editar"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <button
                                            wire:click="delete({{ $holiday->id }})"
                                            wire:confirm="¿Está seguro de eliminar este día festivo?"
                                            class="text-red-600 hover:text-red-900"
                                            title="Eliminar"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $holidays->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay festivos registrados</h3>
                <p class="mt-1 text-sm text-gray-500">Comience agregando un día festivo.</p>
                <div class="mt-6">
                    <button
                        type="button"
                        wire:click.prevent="openCreateModal"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Agregar Festivo
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
