<div class="p-6 max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Solicitar préstamo</h1>
        <a href="{{ route('prestamos.index') }}" class="btn-outline">Volver</a>
    </div>
 {{-- usar componente de status-alert para feedback en la vista --}}
                <div class="mt-2">
                    <x-status-alert :type="$status_type" :message="$status_message" />
                </div>
    <div class="bg-white shadow rounded-lg p-6">

        {{-- Paso 1: formulario de creación del préstamo --}}
        @if($step == 1)
            <form class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <h2 class="text-lg font-semibold">Paso 1 — Crear préstamo</h2>
                    <p class="text-sm text-gray-600 mt-1">Completa los datos del préstamo. Después pulsa Crear para generar el folio y pasar al paso de vinculación.</p>
                </div>

                <div>
                    <label class="field-label">Producto</label>
                    <select wire:model="producto" class="input-project">
                        <option value="individual">Individual</option>
                        <option value="grupal">Grupal</option>
                    </select>
                </div>

                <div>
                    <label class="field-label">Plazo</label>
                    <select wire:model="plazo" class="input-project">
                        <option value="4meses">4 meses</option>
                        <option value="4mesesD">4 meses D</option>
                        <option value="5mesesD">5 meses D</option>
                        <option value="6meses">6 meses</option>
                        <option value="1ano">1 año</option>
                    </select>
                </div>

                <div>
                    <label class="field-label">Periodicidad</label>
                    <select wire:model="periodicidad" class="input-project">
                        <option value="semanal">Semanal</option>
                        <option value="catorcenal">Catorcenal</option>
                        <option value="quincenal">Quincenal</option>
                    </select>
                </div>

                <div>
                    <label class="field-label">Fecha de entrega</label>
                    <input wire:model="fecha_entrega" type="date" class="input-project" />
                </div>

                <div>
                    <label class="field-label">Fecha primer pago (opcional)</label>
                    <input wire:model="fecha_primer_pago" type="date" class="input-project" />
                </div>

                <!-- Monto total eliminado del formulario, se gestiona en la vinculación -->

                <div>
                    <label class="field-label">Tasa de interés (%)</label>
                    @php $isAdmin = auth()->check() && auth()->user()->hasRole('Administrador'); @endphp
                    <input wire:model="tasa_interes" type="number" step="0.01" class="input-project" @if(! $isAdmin) disabled @endif />
                    <div class="text-xs text-gray-500 mt-1">+ IVA</div>
                </div>

                <div>
                    <label class="field-label">Día de pago</label>
                    <select wire:model="dia_pago" class="input-project">
                        <option value="lunes">Lunes</option>
                        <option value="martes">Martes</option>
                        <option value="miercoles">Miércoles</option>
                        <option value="jueves">Jueves</option>
                        <option value="viernes">Viernes</option>
                    </select>
                </div>

                <div class="sm:col-span-2 flex justify-end gap-2 mt-2">
                    <a href="{{ route('prestamos.index') }}" class="btn-outline">Cancelar</a>
                    @if(! empty($prestamo) && $prestamo->id)
                        <button type="button" wire:click.prevent="updatePrestamo" class="btn-primary">Actualizar Paso 1</button>
                        <button type="button" wire:click.prevent="crearPrestamo" class="btn-primary">Ir a Vinculación</button>
                    @else
                        <button type="button" wire:click.prevent="crearPrestamo" class="btn-primary">Crear</button>
                    @endif
                </div>
            </form>
        @endif

        {{-- Paso 2: vincular clientes (después de crear préstamo) --}}
        @if($step == 2)
            <div>
                {{-- Depuración: estado actual del componente (temporal) --}}
                <div class="mb-3 text-sm text-gray-500">Estado: <strong>producto</strong>={{ $producto ?? 'n/a' }}, <strong>step</strong>={{ $step }}</div>
                <h2 class="text-lg font-semibold">Paso 2 — Vincular clientes</h2>
                <p class="text-sm text-gray-600">Préstamo creado con folio: <strong>{{ optional($prestamo)->folio }}</strong></p>

                @if($producto === 'individual')
                    <div class="mt-4">
                        <label class="field-label">Cliente</label>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click.prevent="$set('showClienteModal', true)" class="btn-outline">Buscar cliente</button>
                            @if($cliente_nombre_selected)
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-sm">{{ $cliente_nombre_selected }}</span>
                            @endif
                            <button type="button" wire:click.prevent="$toggle('showNewClienteForm')" class="btn-outline">Nuevo cliente</button>
                        </div>

                        @if($showNewClienteForm)
                            <div class="mt-2 p-3 border rounded bg-gray-50 w-full sm:w-1/2">
                                <label class="field-label">Apellido paterno</label>
                                <input wire:model.defer="new_apellido_paterno" class="input-project" />
                                <label class="field-label mt-2">Apellido materno</label>
                                <input wire:model.defer="new_apellido_materno" class="input-project" />
                                <label class="field-label mt-2">Nombres</label>
                                <input wire:model.defer="new_nombres" class="input-project" />
                                <label class="field-label mt-2">CURP</label>
                                <input wire:model.defer="new_curp" class="input-project" />
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" wire:click.prevent="addNewClient" class="btn-primary">Crear y seleccionar</button>
                                </div>
                            </div>
                        @endif

                        <div class="mt-4 flex gap-2">
                            <button type="button" wire:click.prevent="linkClienteIndividual({{ $monto ?? 0 }})" class="btn-primary">Vincular y finalizar</button>
                            <button type="button" wire:click.prevent="$set('step', 1)" class="btn-outline">Editar Paso 1</button>
                            <a href="{{ route('prestamos.index') }}" class="btn-outline">Volver al listado</a>
                        </div>
                    </div>
                @endif

                @if($producto === 'grupal')
                    <div class="mt-4">
                        <p class="text-sm text-gray-600">Agregar clientes al préstamo grupal.</p>

                        @if(! $grupo_id && empty($clientesAgregados))
                            <div class="mt-3 flex gap-2">
                                    <button type="button" wire:click.prevent="$set('showGrupoModal', true)" class="btn-outline">Buscar grupo</button>
                                    <button type="button" wire:click.prevent="openNewGrupoForm" class="btn-outline">Nuevo grupo</button>
                            </div>

                            @if($showNewGrupoForm)
                                <div class="mt-2 p-3 border rounded bg-gray-50 w-full sm:w-1/2">
                                    <label class="field-label">Nombre del grupo</label>
                                    <input wire:model.defer="new_grupo_nombre" placeholder="{{ $suggested_grupo_name ?? 'Ej: Grupo A' }}" class="input-project" />
                                    <label class="field-label mt-2">Descripción</label>
                                    <textarea wire:model.defer="new_grupo_descripcion" class="input-project"></textarea>
                                    @if(! empty($group_name_suggestions))
                                        <div class="mt-2 text-sm text-gray-600">Sugerencias:</div>
                                        <div class="mt-1 flex gap-2">
                                            @foreach($group_name_suggestions as $s)
                                                <button type="button" wire:click.prevent="selectSuggestedGroupName('{{ $s }}')" class="px-2 py-1 bg-gray-100 rounded text-sm">{{ $s }}</button>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="mt-2 flex justify-end gap-2">
                                        <button type="button" wire:click.prevent="addNewGrupo" class="btn-primary">Crear y continuar</button>
                                    </div>
                                </div>
                            @endif

                        @else
                            <div class="mt-3 flex items-center gap-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-800 text-sm">{{ $grupo_nombre_selected ?? ('Miembros vinculados (' . count($clientesAgregados) . ')') }}</span>
                                <button type="button" wire:click.prevent="$set('showClienteModal', true)" class="btn-outline">Agregar miembros</button>
                                <button type="button" wire:click.prevent="$set('grupo_id', null)" class="btn-outline">Deseleccionar grupo</button>
                            </div>

                            <div class="mt-4">
                                <table class="w-full table-auto border">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="p-2 text-left">Miembro</th>
                                            <th class="p-2 text-left">Monto solicitado</th>
                                            <th class="p-2">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($clientesAgregados as $index => $row)
                                            <tr class="border-t">
                                                <td class="p-2">{{ $row['nombre'] ?? 'Cliente #' . $row['cliente_id'] }}</td>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" wire:model.defer="clientesAgregados.{{ $index }}.monto_solicitado" class="input-project w-32" />
                                                </td>
                                                <td class="p-2 text-center">
                                                    <button type="button" wire:click.prevent="guardarMiembro({{ $index }})" class="btn-outline">Guardar</button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td class="p-2" colspan="3">No hay miembros aún. Usa "Agregar miembros" para buscarlos.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($errors->has('miembros'))
                                <div class="mt-3 text-sm text-red-600">{{ $errors->first('miembros') }}</div>
                            @endif

                            <div class="mt-4 flex gap-2 items-center">
                                <button
                                    type="button"
                                    wire:click.prevent="finalizarVinculacionGrupo"
                                    wire:loading.attr="disabled"
                                    wire:target="finalizarVinculacionGrupo"
                                    class="btn-primary flex items-center gap-2"
                                >
                                    <svg wire:loading.inline wire:target="finalizarVinculacionGrupo" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    <span wire:loading.remove wire:target="finalizarVinculacionGrupo">Finalizar vinculación</span>
                                    <span wire:loading.inline wire:target="finalizarVinculacionGrupo">Procesando...</span>
                                </button>
                                <button type="button" wire:click.prevent="$set('step', 1)" class="btn-outline">Editar Paso 1</button>
                                <a href="{{ route('prestamos.index') }}" class="btn-outline">Volver al listado</a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif
        {{-- Modales: Buscar cliente / Buscar grupo --}}
        @if($showClienteModal)
            <div class="fixed inset-0 z-50 flex items-start justify-center p-4">
                <div class="fixed inset-0 bg-black/50" wire:click="$set('showClienteModal', false)"></div>
                <div class="relative bg-white rounded-lg shadow-lg w-full max-w-2xl p-4 z-10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Buscar cliente</h3>
                        <button type="button" wire:click="$set('showClienteModal', false)" class="text-gray-600">Cerrar</button>
                    </div>

                    <div class="mt-3">
                        <input wire:model.debounce.300ms="clienteSearch" placeholder="Buscar por nombre o ID" class="input-project w-full" />
                    </div>

                    <div class="mt-3 max-h-64 overflow-auto">
                        @if($clientes->isEmpty())
                            <div class="text-sm text-gray-500">No se encontraron clientes.</div>
                        @else
                            <ul class="space-y-2">
                                @foreach($clientes as $c)
                                    <li class="flex items-center justify-between p-2 border rounded">
                                        <div>
                                            <div class="font-medium">{{ trim("{$c->nombres} {$c->apellido_paterno} {$c->apellido_materno}") }}</div>
                                            <div class="text-xs text-gray-500">ID: {{ $c->id }} · CURP: {{ $c->curp }}</div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" wire:click.prevent="selectCliente({{ $c->id }})" class="btn-primary">Seleccionar</button>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if($showGrupoModal)
            <div class="fixed inset-0 z-50 flex items-start justify-center p-4">
                <div class="fixed inset-0 bg-black/50" wire:click="$set('showGrupoModal', false)"></div>
                <div class="relative bg-white rounded-lg shadow-lg w-full max-w-2xl p-4 z-10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Buscar grupo</h3>
                        <button type="button" wire:click="$set('showGrupoModal', false)" class="text-gray-600">Cerrar</button>
                    </div>

                    <div class="mt-3">
                        <input wire:model.debounce.300ms="grupoSearch" placeholder="Buscar por nombre o ID" class="input-project w-full" />
                    </div>

                    <div class="mt-3 max-h-64 overflow-auto">
                        @if($grupos->isEmpty())
                            <div class="text-sm text-gray-500">No se encontraron grupos.</div>
                        @else
                            <ul class="space-y-2">
                                @foreach($grupos as $g)
                                    <li class="flex items-center justify-between p-2 border rounded">
                                        <div>
                                            <div class="font-medium">{{ $g->nombre }}</div>
                                            <div class="text-xs text-gray-500">ID: {{ $g->id }}</div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" wire:click.prevent="selectGrupo({{ $g->id }})" class="btn-primary">Seleccionar</button>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
