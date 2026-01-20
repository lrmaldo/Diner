<div class="p-6 max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-project-700 dark:text-project-300">Crear cliente</h1>
        <a href="{{ route('clients.index') }}" class="btn-outline">Volver</a>
    </div>

    <x-status-alert :type="session('success') ? 'success' : 'info'" :message="session('success')" :timeout="4000" />

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <form wire:submit.prevent="save" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="field-label">Apellido paterno <span class="text-red-600">*</span></label>
                <input wire:model.defer="apellido_paterno" type="text" class="input-project" />
                @error('apellido_paterno') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Apellido materno</label>
                <input wire:model.defer="apellido_materno" type="text" class="input-project" />
                @error('apellido_materno') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="field-label">Nombres <span class="text-red-600">*</span></label>
                <input wire:model.defer="nombres" type="text" class="input-project" />
                @error('nombres') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">CURP <span class="text-red-600">*</span></label>
                <input wire:model.defer="curp" type="text" class="input-project" maxlength="18" pattern="[A-Za-z0-9]{0,18}"
                       oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g,'').slice(0,18)" />
                @error('curp') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Email</label>
                <input wire:model.defer="email" type="email" class="input-project" />
                @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">País de nacimiento</label>
                <input wire:model.defer="pais_nacimiento" type="text" class="input-project" />
                @error('pais_nacimiento') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div x-data="{ estadoCivil: @entangle('estado_civil') }" class="sm:col-span-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Estado civil</label>
                        <select x-model="estadoCivil" class="input-project" aria-label="Estado civil">
                            <option value="">-- Seleccionar --</option>
                            <option value="soltero">Soltero/a</option>
                            <option value="casado">Casado/a</option>
                            <option value="divorciado">Divorciado/a</option>
                            <option value="viudo">Viudo/a</option>
                            <option value="union_libre">Unión libre</option>
                        </select>
                        @error('estado_civil') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="field-label">Nombre del cónyuge</label>
                        <input wire:model.defer="nombre_conyuge" :disabled="estadoCivil === 'soltero'" type="text" class="input-project" />
                        @error('nombre_conyuge') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2">
                <label class="field-label">Calle y número <span class="text-red-600">*</span></label>
                <input wire:model.defer="calle_numero" type="text" class="input-project" />
                @error('calle_numero') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="field-label">Referencia domiciliaria</label>
                <textarea wire:model.defer="referencia_domiciliaria" class="input-project" rows="2"></textarea>
                @error('referencia_domiciliaria') <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Estado civil manejado más arriba con Alpine/entangle (evitar duplicado) --}}

            <div>
                <label class="field-label">Dependientes económicos</label>
                <input wire:model.defer="dependientes_economicos" type="number" class="input-project" min="0" />
                @error('dependientes_economicos') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Nombre de aval</label>
                <input wire:model.defer="nombre_aval" type="text" class="input-project" />
                @error('nombre_aval') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Actividad productiva</label>
                <input wire:model.defer="actividad_productiva" type="text" class="input-project" />
                @error('actividad_productiva') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Años de experiencia</label>
                <input wire:model.defer="anios_experiencia" type="number" class="input-project" min="0" />
                @error('anios_experiencia') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Ingreso mensual</label>
                <input wire:model.defer="ingreso_mensual" type="number" step="0.01" class="input-project" />
                @error('ingreso_mensual') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Gasto mensual familiar</label>
                <input wire:model.defer="gasto_mensual_familiar" type="number" step="0.01" class="input-project" />
                @error('gasto_mensual_familiar') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Crédito solicitado</label>
                <input wire:model.defer="credito_solicitado" type="number" step="1000" class="input-project" />
                @error('credito_solicitado') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Estado</label>
                <input wire:model.defer="estado" type="text" class="input-project" />
                @error('estado') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Municipio</label>
                <input wire:model.defer="municipio" type="text" class="input-project" />
                @error('municipio') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Colonia</label>
                <input wire:model.defer="colonia" type="text" class="input-project" />
                @error('colonia') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Código Postal</label>
                <input wire:model.defer="codigo_postal" type="text" class="input-project" />
                @error('codigo_postal') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Teléfono Celular <span class="text-red-600">*</span></label>
                <input wire:model.defer="telefono_celular" type="text" class="input-project" placeholder="10 dígitos" />
                @error('telefono_celular') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="field-label">Teléfono de Casa</label>
                <input wire:model.defer="telefono_casa" type="text" class="input-project" placeholder="Opcional" />
                @error('telefono_casa') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2 flex justify-end gap-2 mt-2">
                <a href="{{ route('clients.index') }}" class="btn-outline">Cancelar</a>
                <button type="submit" class="btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
