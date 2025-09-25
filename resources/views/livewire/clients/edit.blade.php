<div class="p-6 max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-project-700">Editar cliente</h1>
        <a href="{{ route('clients.index') }}" class="btn-outline">Volver</a>
    </div>

    {{-- Debug: mostrar si el modelo cliente llegó al template --}}
    <div class="mb-4">
        @if(isset($cliente) && $cliente)
            <div class="text-sm text-gray-600">Editando cliente ID: <strong>{{ $cliente->id }}</strong> — {{ $cliente->nombres ?? '(sin nombre)' }}</div>
        @else
            <div class="text-sm text-red-600">No se recibió el modelo <code>$cliente</code>. Revisa la ruta y el componente Livewire.</div>
        @endif

        @if($errors->any())
            <div class="mt-2 text-sm text-red-600">Errores: {{ implode(', ', $errors->all()) }}</div>
        @endif
    </div>

    @if (session('success'))
        <div class="callout-project mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg p-6">
        <form wire:submit.prevent="save" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="field-label">Apellido paterno</label>
                <input wire:model.defer="apellido_paterno" type="text" class="input-project" value="{{ old('apellido_paterno', $apellido_paterno) }}" />
                @error('apellido_paterno') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Apellido materno</label>
                <input wire:model.defer="apellido_materno" type="text" class="input-project" value="{{ old('apellido_materno', $apellido_materno) }}" />
                @error('apellido_materno') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="field-label">Nombres</label>
                <input wire:model.defer="nombres" type="text" class="input-project" value="{{ old('nombres', $nombres) }}" />
                @error('nombres') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">CURP</label>
                <input wire:model.defer="curp" type="text" class="input-project" value="{{ old('curp', $curp) }}" maxlength="18" pattern="[A-Za-z0-9]{0,18}"
                       oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g,'').slice(0,18)" />
                @error('curp') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Email</label>
                <input wire:model.defer="email" type="email" class="input-project" value="{{ old('email', $email) }}" />
                @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">País de nacimiento</label>
                <input wire:model.defer="pais_nacimiento" type="text" class="input-project" value="{{ old('pais_nacimiento', $pais_nacimiento) }}" />
                @error('pais_nacimiento') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Nombre del cónyuge</label>
                <input wire:model.defer="nombre_conyuge" type="text" class="input-project" value="{{ old('nombre_conyuge', $nombre_conyuge) }}" />
                @error('nombre_conyuge') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="field-label">Calle y número</label>
                <input wire:model.defer="calle_numero" type="text" class="input-project" value="{{ old('calle_numero', $calle_numero) }}" />
                @error('calle_numero') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="field-label">Referencia domiciliaria</label>
                <textarea wire:model.defer="referencia_domiciliaria" class="input-project" rows="2">{{ old('referencia_domiciliaria', $referencia_domiciliaria) }}</textarea>
                @error('referencia_domiciliaria') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Estado civil</label>
                <select wire:model.defer="estado_civil" class="input-project">
                    <option value="">-- Seleccionar --</option>
                    <option value="soltero" {{ old('estado_civil', $estado_civil) === 'soltero' ? 'selected' : '' }}>Soltero/a</option>
                    <option value="casado" {{ old('estado_civil', $estado_civil) === 'casado' ? 'selected' : '' }}>Casado/a</option>
                    <option value="divorciado" {{ old('estado_civil', $estado_civil) === 'divorciado' ? 'selected' : '' }}>Divorciado/a</option>
                    <option value="viudo" {{ old('estado_civil', $estado_civil) === 'viudo' ? 'selected' : '' }}>Viudo/a</option>
                    <option value="union_libre" {{ old('estado_civil', $estado_civil) === 'union_libre' ? 'selected' : '' }}>Unión libre</option>
                </select>
                @error('estado_civil') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Dependientes económicos</label>
                <input wire:model.defer="dependientes_economicos" type="number" class="input-project" min="0" value="{{ old('dependientes_economicos', $dependientes_economicos) }}" />
                @error('dependientes_economicos') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Nombre de aval</label>
                <input wire:model.defer="nombre_aval" type="text" class="input-project" value="{{ old('nombre_aval', $nombre_aval) }}" />
                @error('nombre_aval') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Actividad productiva</label>
                <input wire:model.defer="actividad_productiva" type="text" class="input-project" value="{{ old('actividad_productiva', $actividad_productiva) }}" />
                @error('actividad_productiva') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Años de experiencia</label>
                <input wire:model.defer="anios_experiencia" type="number" class="input-project" min="0" value="{{ old('anios_experiencia', $anios_experiencia) }}" />
                @error('anios_experiencia') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Ingreso mensual</label>
                <input wire:model.defer="ingreso_mensual" type="number" step="0.01" class="input-project" value="{{ old('ingreso_mensual', $ingreso_mensual) }}" />
                @error('ingreso_mensual') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Gasto mensual familiar</label>
                <input wire:model.defer="gasto_mensual_familiar" type="number" step="0.01" class="input-project" value="{{ old('gasto_mensual_familiar', $gasto_mensual_familiar) }}" />
                @error('gasto_mensual_familiar') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Crédito solicitado</label>
                <input wire:model.defer="credito_solicitado" type="number" step="0.01" class="input-project" value="{{ old('credito_solicitado', $credito_solicitado) }}" />
                @error('credito_solicitado') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Estado</label>
                <input wire:model.defer="estado" type="text" class="input-project" value="{{ old('estado', $estado) }}" />
                @error('estado') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Municipio</label>
                <input wire:model.defer="municipio" type="text" class="input-project" value="{{ old('municipio', $municipio) }}" />
                @error('municipio') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Colonia</label>
                <input wire:model.defer="colonia" type="text" class="input-project" value="{{ old('colonia', $colonia) }}" />
                @error('colonia') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Código Postal</label>
                <input wire:model.defer="codigo_postal" type="text" class="input-project" value="{{ old('codigo_postal', $codigo_postal) }}" />
                @error('codigo_postal') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="field-label">Teléfono</label>
                <div class="space-y-2">
                    @foreach($phones as $i => $p)
                        <div class="flex gap-2 items-center w-full flex-wrap">
                            <div class="flex-none">
                                <select wire:model.defer="phones.{{ $i }}.tipo" class="input-project w-28">
                                    <option value="celular">Celular</option>
                                    <option value="casa">Casa</option>
                                    <option value="trabajo">Trabajo</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>

                            <div class="flex-1 min-w-0">
                                <input wire:model.defer="phones.{{ $i }}.numero" type="text" class="input-project w-full" style="min-width:0;" placeholder="Número" />
                            </div>

                            <div class="flex-none">
                                <button type="button" wire:click.prevent="removePhone({{ $i }})" class="btn-outline">Eliminar</button>
                            </div>
                        </div>
                    @endforeach

                    <div>
                        <button type="button" wire:click.prevent="addPhone" class="btn-primary">Agregar teléfono</button>
                    </div>
                </div>
                @error('phones.*.numero') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Modal de confirmación para eliminar teléfono --}}
            @if($phoneToDeleteIndex !== null)
                <div class="modal-backdrop"></div>
                <div class="fixed inset-0 flex items-center justify-center z-50">
                    <div class="modal-panel max-w-lg w-full p-6">
                        <h3 class="text-lg font-semibold mb-4">Confirmar eliminación</h3>
                        <p class="mb-4">¿Deseas eliminar el teléfono <strong>{{ $phones[$phoneToDeleteIndex]['numero'] ?? '' }}</strong>?</p>
                        <div class="flex justify-end gap-2">
                            <button wire:click="cancelRemovePhone" class="btn-outline">Cancelar</button>
                            <button wire:click="confirmRemovePhone" class="btn-danger">Eliminar</button>
                        </div>
                    </div>
                </div>
            @endif

            <div class="sm:col-span-2 flex justify-end gap-2 mt-2">
                <a href="{{ route('clients.index') }}" class="btn-outline">Cancelar</a>
                <button type="submit" class="btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
