<div class="p-6">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Configuraciones del Sistema</h2>
        <p class="mt-1 text-sm text-gray-600">Gestiona las configuraciones principales del sistema</p>
    </div>

    @if($showSuccessMessage)
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
            Configuración actualizada correctamente.
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

    <!-- Configuraciones Financieras -->
    <div class="mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
            </svg>
            Configuraciones Financieras
        </h3>

        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @foreach($configurations as $config)
                    @if($config['category'] === 'financial')
                        <li class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $config['description'] }}
                                        </p>
                                        @if($config['editable'])
                                            <div class="flex items-center space-x-2">
                                                @if($editingConfig === $config['id'])
                                                    <div class="flex items-center space-x-2">
                                                        <input
                                                            type="{{ $config['type'] === 'decimal' || $config['type'] === 'integer' ? 'number' : 'text' }}"
                                                            wire:model="editValue"
                                                            {{ $config['type'] === 'decimal' ? 'step=0.01' : '' }}
                                                            class="w-24 px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                        >
                                                        <button
                                                            wire:click="saveConfiguration({{ $config['id'] }})"
                                                            class="inline-flex items-center px-2 py-1 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700"
                                                        >
                                                            Guardar
                                                        </button>
                                                        <button
                                                            wire:click="cancelEditing"
                                                            class="inline-flex items-center px-2 py-1 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                                                        >
                                                            Cancelar
                                                        </button>
                                                    </div>
                                                @else
                                                    <div class="flex items-center space-x-3">
                                                        <span class="text-sm font-semibold text-gray-900">
                                                            {{ $config['type'] === 'decimal' ? $config['value'] . '%' : $config['value'] }}
                                                        </span>
                                                        <button
                                                            wire:click="startEditing({{ $config['id'] }})"
                                                            class="text-blue-600 hover:text-blue-900 text-sm"
                                                        >
                                                            Editar
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-sm font-semibold text-gray-900">{{ $config['value'] }}</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">Clave: {{ $config['key'] }}</p>
                                </div>
                            </div>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Configuraciones Generales -->
    <div class="mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Configuraciones Generales
        </h3>

        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @foreach($configurations as $config)
                    @if($config['category'] === 'general')
                        <li class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $config['description'] }}
                                        </p>
                                        @if($config['editable'])
                                            <div class="flex items-center space-x-2">
                                                @if($editingConfig === $config['id'])
                                                    <div class="flex items-center space-x-2">
                                                        <input
                                                            type="text"
                                                            wire:model="editValue"
                                                            class="w-48 px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                        >
                                                        <button
                                                            wire:click="saveConfiguration({{ $config['id'] }})"
                                                            class="inline-flex items-center px-2 py-1 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700"
                                                        >
                                                            Guardar
                                                        </button>
                                                        <button
                                                            wire:click="cancelEditing"
                                                            class="inline-flex items-center px-2 py-1 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                                                        >
                                                            Cancelar
                                                        </button>
                                                    </div>
                                                @else
                                                    <div class="flex items-center space-x-3">
                                                        <span class="text-sm font-semibold text-gray-900">{{ $config['value'] }}</span>
                                                        <button
                                                            wire:click="startEditing({{ $config['id'] }})"
                                                            class="text-blue-600 hover:text-blue-900 text-sm"
                                                        >
                                                            Editar
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-sm font-semibold text-gray-900">{{ $config['value'] }}</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">Clave: {{ $config['key'] }}</p>
                                </div>
                            </div>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>

    <div class="text-sm text-gray-500">
        <p><strong>Nota:</strong> Las configuraciones marcadas como no editables son valores del sistema que no se pueden modificar desde esta interfaz.</p>
    </div>
</div>
