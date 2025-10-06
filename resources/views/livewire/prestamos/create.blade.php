<div class="p-6 max-w-4xl mx-auto">
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('prestamo-actualizado', () => {
                console.log('Préstamo actualizado. Actualizando interfaz...');
                // Forzar actualización de Livewire (alternativa)
                // window.location.reload();
            });
        });
    </script>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Solicitar préstamo</h1>
        <a href="{{ route('prestamos.index') }}" class="btn-outline">Volver</a>
    </div>

    {{-- Componente de status-alert para feedback en la vista --}}
    <div class="mt-2">
        @if($status_message)
            <div class="animate-pulse">
                <x-status-alert :type="$status_type" :message="$status_message" :timeout="0" />
            </div>
        @endif
    </div>

    <div class="bg-white shadow rounded-lg p-6">

        {{-- Paso 1: formulario de creación del préstamo --}}
        @if($step == 1)
            <form class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <h2 class="text-lg font-semibold">Paso 1 — Crear préstamo</h2>
                    <p class="text-sm text-gray-600 mt-1">Completa los datos del préstamo. Después pulsa Crear para generar el folio y pasar al paso de agregar clientes.</p>
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

                <!-- Monto total eliminado del formulario, se gestiona al agregar clientes -->

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
                        <button type="button" wire:click.prevent="crearPrestamo" class="btn-primary">Ir a agregar clientes</button>
                    @else
                        <button type="button" wire:click.prevent="crearPrestamo" class="btn-primary">Crear</button>
                    @endif
                </div>
            </form>
        @endif

        {{-- Paso 2: resumen + agregar clientes (después de crear préstamo) --}}
        @if($step == 2)
            <div>
                {{-- Depuración: estado actual del componente (temporal) --}}
                <div class="mb-3 text-sm text-gray-500">Estado: <strong>producto</strong>={{ $producto ?? 'n/a' }}, <strong>step</strong>={{ $step }}</div>
                <h2 class="text-lg font-semibold">Paso 2 — Agregar clientes</h2>
                <p class="text-sm text-gray-600">Préstamo creado con folio: <strong>{{ optional($prestamo)->folio }}</strong></p>

                {{-- Bloque resumen no editable del préstamo --}}
                <div class="mt-3 border rounded p-4 bg-gray-50">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-sm">
                        <div><span class="text-gray-500">Producto:</span> <span class="font-medium">{{ $producto }}</span></div>
                        <div><span class="text-gray-500">Plazo:</span> <span class="font-medium">{{ $plazo }}</span></div>
                        <div><span class="text-gray-500">Periodicidad:</span> <span class="font-medium">{{ $periodicidad }}</span></div>
                        <div><span class="text-gray-500">Fecha de entrega:</span> <span class="font-medium">{{ $fecha_entrega }}</span></div>
                        <div><span class="text-gray-500">Fecha primer pago:</span> <span class="font-medium">{{ $fecha_primer_pago ?: '—' }}</span></div>
                        <div><span class="text-gray-500">Tasa de interés:</span> <span class="font-medium">{{ number_format((float) $tasa_interes, 2) }}%</span></div>
                        <div><span class="text-gray-500">Día de pago:</span> <span class="font-medium">{{ ucfirst($dia_pago) }}</span></div>
                        <div class="sm:col-span-2">
                            @php
                                $estado = optional($prestamo)->estado ?? 'en_curso';
                                $map = [
                                    'en_curso' => 'bg-yellow-100 text-yellow-800',
                                    'en_revision' => 'bg-blue-100 text-blue-800',
                                    'autorizado' => 'bg-green-100 text-green-800',
                                    'rechazado' => 'bg-red-100 text-red-800',
                                ];
                                $cls = $map[$estado] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-block px-2 py-1 rounded text-sm {{ $cls }}">Estado: {{ str_replace('_', ' ', $estado) }}</span>
                        </div>
                        @if($producto === 'individual')
                            <div class="sm:col-span-3">
                                <div class="text-gray-500">Monto del préstamo:</div>
                                <div class="font-extrabold text-3xl text-green-700">$ {{ number_format((float) ($monto ?? 0), 2) }}</div>
                                @if(!empty($cliente_nombre_selected))
                                    <div class="text-sm text-gray-600 mt-1">Para: <span class="font-medium">{{ $cliente_nombre_selected }}</span></div>
                                @endif
                            </div>
                        @elseif($producto === 'grupal')
                            @php
                                $__totalGrupo = 0.0;
                                if (isset($clientesAgregados) && is_array($clientesAgregados)) {
                                    foreach ($clientesAgregados as $__row) {
                                        $__totalGrupo += (float) ($__row['monto_solicitado'] ?? 0);
                                    }
                                } elseif (isset($monto_total)) {
                                    $__totalGrupo = (float) $monto_total;
                                }
                            @endphp
                            <div class="sm:col-span-3">
                                <div class="text-gray-500">Monto total del grupo:</div>
                                <div class="font-extrabold text-3xl text-green-700">$ {{ number_format($__totalGrupo, 2) }}</div>
                                @if(!empty($grupo_nombre_selected))
                                    <div class="text-sm text-gray-600 mt-1">Grupo: <span class="font-medium">{{ $grupo_nombre_selected }}</span></div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="mt-3 flex justify-end">
                        <button type="button" wire:click.prevent="$set('step', 1)" class="btn-outline">Editar préstamo</button>
                    </div>
                </div>

                @if($producto === 'individual')
                    <div class="mt-4">
                        <label class="field-label">Cliente</label>
                        <div class="flex items-center gap-2 flex-wrap">
                            <button type="button" wire:click.prevent="$set('showClienteModal', true)" class="btn-outline">Buscar cliente</button>
                            <button type="button" wire:click.prevent="$toggle('showNewClienteForm')" class="btn-outline">Nuevo cliente</button>

                            @if($cliente_nombre_selected)
                                <div class="flex items-center mt-2 w-full">
                                    <div class="flex-grow p-3 bg-blue-50 border border-blue-200 rounded-l-lg">
                                        <span class="font-medium text-blue-800">{{ $cliente_nombre_selected }}</span>
                                    </div>
                                    <button type="button" wire:click.prevent="openEditCliente({{ $cliente_id }})" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                    </button>
                                    <button type="button" wire:click.prevent="removeCliente" class="bg-red-500 hover:bg-red-600 text-white px-4 py-3 rounded-r-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>

                        @if($showNewClienteForm)
                            <div class="mt-2 p-3 border rounded bg-gray-50 w-full sm:w-2/3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="field-label">Apellido paterno *</label>
                                        <input wire:model.defer="new_apellido_paterno" class="input-project" />
                                    </div>
                                    <div>
                                        <label class="field-label">Apellido materno</label>
                                        <input wire:model.defer="new_apellido_materno" class="input-project" />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="field-label">Nombres *</label>
                                        <input wire:model.defer="new_nombres" class="input-project" />
                                    </div>
                                    <div>
                                        <label class="field-label">CURP *</label>
                                        <input wire:model.defer="new_curp" class="input-project" maxlength="18" />
                                    </div>
                                    <div>
                                        <label class="field-label">Email</label>
                                        <input wire:model.defer="new_email" type="email" class="input-project" />
                                    </div>
                                    <div>
                                        <label class="field-label">País de nacimiento</label>
                                        <input wire:model.defer="new_pais_nacimiento" class="input-project" />
                                    </div>
                                    <div>
                                        <label class="field-label">Estado civil</label>
                                        <select wire:model.defer="new_estado_civil" class="input-project">
                                            <option value="">-- Seleccionar --</option>
                                            <option value="soltero">Soltero/a</option>
                                            <option value="casado">Casado/a</option>
                                            <option value="divorciado">Divorciado/a</option>
                                            <option value="viudo">Viudo/a</option>
                                            <option value="union_libre">Unión libre</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="field-label">Nombre del cónyuge</label>
                                        <input wire:model.defer="new_nombre_conyuge" class="input-project" />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="field-label">Calle y número *</label>
                                        <input wire:model.defer="new_calle_numero" class="input-project" />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="field-label">Referencia domiciliaria</label>
                                        <textarea wire:model.defer="new_referencia_domiciliaria" class="input-project" rows="2"></textarea>
                                    </div>
                                    <div>
                                        <label class="field-label">Dependientes económicos</label>
                                        <input wire:model.defer="new_dependientes_economicos" type="number" min="0" class="input-project" />
                                    </div>
                                    <div>
                                        <label class="field-label">Nombre de aval</label>
                                        <input wire:model.defer="new_nombre_aval" class="input-project" />
                                    </div>
                                    <div>
                                        <label class="field-label">Actividad productiva</label>
                                        <input wire:model.defer="new_actividad_productiva" class="input-project" />
                                    </div>
                                    <div>
                                        <label class="field-label">Años de experiencia</label>
                                        <input wire:model.defer="new_anios_experiencia" type="number" min="0" class="input-project" />
                                    </div>
                                    <div>
                                        <label class="field-label">Ingreso mensual</label>
                                        <input wire:model.defer="new_ingreso_mensual" type="number" step="0.01" class="input-project" />
                                    </div>
                                    <div>
                                        <label class="field-label">Gasto mensual familiar</label>
                                        <input wire:model.defer="new_gasto_mensual_familiar" type="number" step="0.01" class="input-project" />
                                    </div>
                                    <div>
                                        <label class="field-label">Crédito solicitado</label>
                                        <input wire:model.defer="new_credito_solicitado" type="number" step="0.01" class="input-project" />
                                    </div>
                                    <div>
                                        <label class="field-label">Estado</label>
                                        <input wire:model.defer="new_estado" class="input-project" />
                                    </div>
                                    <div>
                                        <label class="field-label">Municipio</label>
                                        <input wire:model.defer="new_municipio" class="input-project" />
                                    </div>
                                    <div>
                                        <label class="field-label">Colonia</label>
                                        <input wire:model.defer="new_colonia" class="input-project" />
                                    </div>
                                    <div>
                                        <label class="field-label">Código Postal</label>
                                        <input wire:model.defer="new_codigo_postal" class="input-project" />
                                    </div>
                                </div>
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" wire:click.prevent="addNewClient" class="btn-primary">Crear y seleccionar</button>
                                </div>
                            </div>
                        @endif

                        <div class="mt-4 flex gap-2">
                            <button type="button" wire:click.prevent="enviarAComite" class="btn-primary">Enviar a comité</button>
                            <button type="button" wire:click.prevent="$set('step', 1)" class="btn-outline">Editar Paso 1</button>
                            <a href="{{ route('prestamos.index') }}" class="btn-outline">Volver al listado</a>
                        </div>
                    </div>
                @endif

                @if($producto === 'grupal')
                    <div class="mt-4">
                        <p class="text-sm text-gray-600">Agregar clientes al préstamo grupal.</p>

                        <div class="mt-3 flex items-center gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-800 text-sm">{{ $grupo_nombre_selected ?? 'representante grupal' }}</span>
                            <button type="button" wire:click.prevent="$set('showClienteModal', true)" class="btn-outline">Agregar cliente existente</button>
                            <button type="button" wire:click.prevent="$toggle('showNewClienteForm')" class="btn-outline">Crear cliente nuevo</button>
                        </div>
                        @if($showNewClienteForm)
                                <div class="mt-3 p-4 border rounded bg-gray-50">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="field-label">Apellido paterno *</label>
                                            <input wire:model.defer="new_apellido_paterno" class="input-project" />
                                        </div>
                                        <div>
                                            <label class="field-label">Apellido materno</label>
                                            <input wire:model.defer="new_apellido_materno" class="input-project" />
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="field-label">Nombres *</label>
                                            <input wire:model.defer="new_nombres" class="input-project" />
                                        </div>
                                        <div>
                                            <label class="field-label">CURP *</label>
                                            <input wire:model.defer="new_curp" class="input-project" maxlength="18" />
                                        </div>
                                        <div>
                                            <label class="field-label">Email</label>
                                            <input wire:model.defer="new_email" type="email" class="input-project" />
                                        </div>
                                        <div>
                                            <label class="field-label">País de nacimiento</label>
                                            <input wire:model.defer="new_pais_nacimiento" class="input-project" />
                                        </div>
                                        <div>
                                            <label class="field-label">Estado civil</label>
                                            <select wire:model.defer="new_estado_civil" class="input-project">
                                                <option value="">-- Seleccionar --</option>
                                                <option value="soltero">Soltero/a</option>
                                                <option value="casado">Casado/a</option>
                                                <option value="divorciado">Divorciado/a</option>
                                                <option value="viudo">Viudo/a</option>
                                                <option value="union_libre">Unión libre</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="field-label">Nombre del cónyuge</label>
                                            <input wire:model.defer="new_nombre_conyuge" class="input-project" />
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="field-label">Calle y número *</label>
                                            <input wire:model.defer="new_calle_numero" class="input-project" />
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="field-label">Referencia domiciliaria</label>
                                            <textarea wire:model.defer="new_referencia_domiciliaria" class="input-project" rows="2"></textarea>
                                        </div>
                                        <div>
                                            <label class="field-label">Dependientes económicos</label>
                                            <input wire:model.defer="new_dependientes_economicos" type="number" min="0" class="input-project" />
                                        </div>
                                        <div>
                                            <label class="field-label">Nombre de aval</label>
                                            <input wire:model.defer="new_nombre_aval" class="input-project" />
                                        </div>
                                        <div>
                                            <label class="field-label">Actividad productiva</label>
                                            <input wire:model.defer="new_actividad_productiva" class="input-project" />
                                        </div>
                                        <div>
                                            <label class="field-label">Años de experiencia</label>
                                            <input wire:model.defer="new_anios_experiencia" type="number" min="0" class="input-project" />
                                        </div>
                                        <div>
                                            <label class="field-label">Ingreso mensual</label>
                                            <input wire:model.defer="new_ingreso_mensual" type="number" step="0.01" class="input-project" />
                                        </div>
                                        <div>
                                            <label class="field-label">Gasto mensual familiar</label>
                                            <input wire:model.defer="new_gasto_mensual_familiar" type="number" step="0.01" class="input-project" />
                                        </div>
                                        <div>
                                            <label class="field-label">Crédito solicitado</label>
                                            <input wire:model.defer="new_credito_solicitado" type="number" step="0.01" class="input-project" />
                                        </div>
                                        <div>
                                            <label class="field-label">Estado</label>
                                            <input wire:model.defer="new_estado" class="input-project" />
                                        </div>
                                        <div>
                                            <label class="field-label">Municipio</label>
                                            <input wire:model.defer="new_municipio" class="input-project" />
                                        </div>
                                        <div>
                                            <label class="field-label">Colonia</label>
                                            <input wire:model.defer="new_colonia" class="input-project" />
                                        </div>
                                        <div>
                                            <label class="field-label">Código Postal</label>
                                            <input wire:model.defer="new_codigo_postal" class="input-project" />
                                        </div>
                                    </div>
                                    <div class="mt-2 flex justify-end gap-2">
                                        <button type="button" wire:click.prevent="addNewClient" class="btn-primary">Crear y seleccionar</button>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-4">
                                <table class="w-full table-auto border">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="p-2 text-left">Miembro</th>
                                            <th class="p-2 text-left">Monto solicitado</th>
                                            <th class="p-2">
                                                Representante
                                                @error('representante')
                                                    <div class="text-xs text-red-600 font-normal">{{ $message }}</div>
                                                @enderror
                                            </th>
                                            <th class="p-2">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($clientesAgregados as $index => $row)
                                            <tr class="border-t" wire:key="miembro-{{ $row['cliente_id'] ?? $index }}">
                                                <td class="p-2">{{ $row['nombre'] ?? 'Cliente #' . $row['cliente_id'] }}</td>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" wire:model.lazy="clientesAgregados.{{ $index }}.monto_solicitado" wire:change="guardarMiembro({{ $index }})" wire:blur="guardarMiembro({{ $index }})" class="input-project w-32" />
                                                </td>
                                                <td class="p-2 text-center">
                                                    <input type="radio" name="representante" value="{{ $row['cliente_id'] }}" wire:click="selectRepresentante({{ $row['cliente_id'] }})" @checked((int)$representante_id === (int)($row['cliente_id'] ?? 0)) />
                                                </td>
                                                <td class="p-2 text-center space-x-2">
                                                    <button type="button" wire:click.prevent="openEditCliente({{ $row['cliente_id'] ?? 0 }})" class="btn-outline">Editar</button>
                                                    <button
                                                        type="button"
                                                        x-data
                                                        @click.prevent="if (confirm('¿Eliminar este miembro del grupo?')) { $wire.eliminarMiembro({{ $index }}); }"
                                                        class="btn-danger"
                                                    >Eliminar</button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td class="p-2" colspan="4">No hay miembros aún. Usa "Agregar cliente existente" o "Crear cliente nuevo".</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($errors->has('miembros'))
                                <div class="mt-3 text-sm text-red-600">{{ $errors->first('miembros') }}</div>
                            @endif

                            <div class="mt-4 flex gap-2 items-center">
                                <button type="button" wire:click.prevent="enviarAComite" class="btn-primary">Enviar a comité</button>
                                <button type="button" wire:click.prevent="$set('step', 1)" class="btn-outline">Editar Paso 1</button>
                                <a href="{{ route('prestamos.index') }}" class="btn-outline">Volver al listado</a>
                            </div>
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

        {{-- Panel inline: Editar cliente seleccionado --}}
        @if($showEditClienteModal)
            <div class="mt-4 p-4 border rounded bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Editar cliente</h3>
                    <button type="button" wire:click="$set('showEditClienteModal', false)" class="text-gray-600">Cerrar</button>
                </div>
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="field-label">Apellido paterno *</label>
                        <input wire:model.defer="edit_apellido_paterno" class="input-project" />
                    </div>
                    <div>
                        <label class="field-label">Apellido materno</label>
                        <input wire:model.defer="edit_apellido_materno" class="input-project" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label">Nombres *</label>
                        <input wire:model.defer="edit_nombres" class="input-project" />
                    </div>
                    <div>
                        <label class="field-label">CURP *</label>
                        <input wire:model.defer="edit_curp" class="input-project" maxlength="18" />
                    </div>
                    <div>
                        <label class="field-label">Email</label>
                        <input wire:model.defer="edit_email" type="email" class="input-project" />
                    </div>
                    <div>
                        <label class="field-label">País de nacimiento</label>
                        <input wire:model.defer="edit_pais_nacimiento" class="input-project" />
                    </div>
                    <div>
                        <label class="field-label">Estado civil</label>
                        <select wire:model.defer="edit_estado_civil" class="input-project">
                            <option value="">-- Seleccionar --</option>
                            <option value="soltero">Soltero/a</option>
                            <option value="casado">Casado/a</option>
                            <option value="divorciado">Divorciado/a</option>
                            <option value="viudo">Viudo/a</option>
                            <option value="union_libre">Unión libre</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Nombre del cónyuge</label>
                        <input wire:model.defer="edit_nombre_conyuge" class="input-project" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label">Calle y número *</label>
                        <input wire:model.defer="edit_calle_numero" class="input-project" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label">Referencia domiciliaria</label>
                        <textarea wire:model.defer="edit_referencia_domiciliaria" class="input-project" rows="2"></textarea>
                    </div>
                    <div>
                        <label class="field-label">Dependientes económicos</label>
                        <input wire:model.defer="edit_dependientes_economicos" type="number" min="0" class="input-project" />
                    </div>
                    <div>
                        <label class="field-label">Nombre de aval</label>
                        <input wire:model.defer="edit_nombre_aval" class="input-project" />
                    </div>
                    <div>
                        <label class="field-label">Actividad productiva</label>
                        <input wire:model.defer="edit_actividad_productiva" class="input-project" />
                    </div>
                    <div>
                        <label class="field-label">Años de experiencia</label>
                        <input wire:model.defer="edit_anios_experiencia" type="number" min="0" class="input-project" />
                    </div>
                    <div>
                        <label class="field-label">Ingreso mensual</label>
                        <input wire:model.defer="edit_ingreso_mensual" type="number" step="0.01" class="input-project" />
                    </div>
                    <div>
                        <label class="field-label">Gasto mensual familiar</label>
                        <input wire:model.defer="edit_gasto_mensual_familiar" type="number" step="0.01" class="input-project" />
                    </div>
                    <div>
                        <label class="field-label">Crédito solicitado</label>
                        <input wire:model.defer="edit_credito_solicitado" type="number" step="0.01" class="input-project" />
                    </div>
                    <div>
                        <label class="field-label">Estado</label>
                        <input wire:model.defer="edit_estado" class="input-project" />
                    </div>
                    <div>
                        <label class="field-label">Municipio</label>
                        <input wire:model.defer="edit_municipio" class="input-project" />
                    </div>
                    <div>
                        <label class="field-label">Colonia</label>
                        <input wire:model.defer="edit_colonia" class="input-project" />
                    </div>
                    <div>
                        <label class="field-label">Código Postal</label>
                        <input wire:model.defer="edit_codigo_postal" class="input-project" />
                    </div>
                </div>
                <div class="mt-3 flex justify-end gap-2">
                    <button type="button" wire:click.prevent="saveEditedCliente" class="btn-primary">Guardar y aplicar</button>
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
