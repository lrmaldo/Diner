<div class="p-6 max-w-6xl mx-auto">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div class="flex items-start gap-3">
            {{-- Icono cliente --}}
            <div class="flex-none mt-1">
                <svg class="w-10 h-10 text-project-600" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M4 20c0-3.314 2.686-6 6-6h4c3.314 0 6 2.686 6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-semibold text-project-700">Detalle del cliente</h1>
                <p class="text-sm text-gray-500 mt-1">Información completa del cliente y contactos</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('clients.index') }}" class="btn-outline">Volver</a>
            <a href="{{ route('clients.edit', $cliente->id) }}" class="btn-primary">Editar</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Left / Main column --}}
        <div class="md:col-span-2 bg-white shadow rounded-lg p-6 space-y-6">
            {{-- Personal --}}
            <section>
                <h2 class="text-lg font-medium text-gray-700 flex items-center gap-2"><svg class="w-5 h-5 text-project-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5z" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 20c0-3.314 2.686-6 6-6h4c3.314 0 6 2.686 6 6" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>Datos personales</h2>
                <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                    <div>
                        <dt class="text-sm text-gray-500">Nombre completo</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ trim("{$cliente->apellido_paterno} {$cliente->apellido_materno} {$cliente->nombres}") ?: '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">CURP</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ $cliente->curp ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Email</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ $cliente->email ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">País de nacimiento</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ $cliente->pais_nacimiento ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Estado civil</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ $cliente->estado_civil ? ucfirst(str_replace('_', ' ', $cliente->estado_civil)) : '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Nombre del cónyuge</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ $cliente->nombre_conyuge ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Dependientes económicos</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ isset($cliente->dependientes_economicos) ? $cliente->dependientes_economicos : '-' }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Contactos / Teléfonos --}}
            <section>
                <h2 class="text-lg font-medium text-gray-700 flex items-center gap-2"><svg class="w-5 h-5 text-project-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M3 8a4 4 0 014-4h10a4 4 0 014 4v8a4 4 0 01-4 4H7a4 4 0 01-4-4V8z" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 13h8" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>Contactos</h2>
                <div class="mt-3 space-y-2">
                    @forelse($cliente->telefonos as $tel)
                        <div class="flex items-center justify-between gap-4 p-3 border rounded-md">
                            <div>
                                <div class="text-sm text-gray-500">{{ ucfirst($tel->tipo) }}</div>
                                <div class="text-base font-medium text-gray-900"> <a href="tel:{{ $tel->numero }}" class="hover:underline">{{ $tel->numero }}</a></div>
                            </div>
                            <div class="text-sm text-gray-500">ID: {{ $tel->id }}</div>
                        </div>
                    @empty
                        <div class="text-gray-700">No hay teléfonos registrados.</div>
                    @endforelse
                </div>
            </section>

            {{-- Dirección --}}
            <section>
                <h2 class="text-lg font-medium text-gray-700 flex items-center gap-2"><svg class="w-5 h-5 text-project-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="9" r="2.5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>Dirección</h2>
                <div class="mt-3 text-gray-900">
                    @if($cliente->calle_numero || $cliente->colonia || $cliente->municipio || $cliente->estado || $cliente->codigo_postal)
                        <div>{{ $cliente->calle_numero ?? '' }}</div>
                        <div class="text-sm text-gray-600">{{ $cliente->colonia ? 'Col. '.$cliente->colonia : '' }} {{ $cliente->municipio ? '· '.$cliente->municipio : '' }}</div>
                        <div class="text-sm text-gray-600">{{ $cliente->estado ?? '' }} {{ $cliente->codigo_postal ? '· C.P. '.$cliente->codigo_postal : '' }}</div>
                    @else
                        -
                    @endif
                </div>
            </section>

            {{-- Información económica y laboral --}}
            <section>
                <h2 class="text-lg font-medium text-gray-700 flex items-center gap-2"><svg class="w-5 h-5 text-project-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 1v22" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 7h14" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M7 11h10" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>Información económica</h2>
                <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                    <div>
                        <dt class="text-sm text-gray-500">Actividad productiva</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ $cliente->actividad_productiva ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Años de experiencia</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ isset($cliente->anios_experiencia) ? $cliente->anios_experiencia : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Ingreso mensual</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ isset($cliente->ingreso_mensual) ? '$'.number_format($cliente->ingreso_mensual, 2) : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Gasto mensual familiar</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ isset($cliente->gasto_mensual_familiar) ? '$'.number_format($cliente->gasto_mensual_familiar, 2) : '-' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-gray-500">Crédito solicitado</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ isset($cliente->credito_solicitado) ? '$'.number_format($cliente->credito_solicitado, 2) : '-' }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Aval y referencias --}}
            <section>
                <h2 class="text-lg font-medium text-gray-700 flex items-center gap-2"><svg class="w-5 h-5 text-project-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 12c2 0 3.5-1.5 3.5-3.5S14 5 12 5s-3.5 1.5-3.5 3.5S10 12 12 12z" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 20c1.333-3 4.667-5 8-5s6.667 2 8 5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>Aval y referencias</h2>
                <div class="mt-3 text-gray-900">
                    <div><span class="text-sm text-gray-500">Nombre de aval</span>
                        <div class="mt-1">{{ $cliente->nombre_aval ?? '-' }}</div>
                    </div>
                    <div class="mt-3"><span class="text-sm text-gray-500">Referencia domiciliaria</span>
                        <div class="mt-1 text-gray-900">{{ $cliente->referencia_domiciliaria ?? '-' }}</div>
                    </div>
                </div>
            </section>
        </div>

        {{-- Right / Side column (summary) --}}
        <aside class="bg-white shadow rounded-lg p-6 space-y-4">
            <div class="stat-card flex items-center gap-3">
                <svg class="w-6 h-6 text-project-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 1v22" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <div>
                    <div class="text-sm text-gray-500">Registrado</div>
                    <div class="text-xl font-semibold text-gray-900">{{ $cliente->created_at ? $cliente->created_at->format('d M Y') : '-' }}</div>
                </div>
            </div>

            <div class="stat-card flex items-center gap-3">
                <svg class="w-6 h-6 text-project-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M3 12h18" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 3v18" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <div>
                    <div class="text-sm text-gray-500">Última actualización</div>
                    <div class="text-xl font-semibold text-gray-900">{{ $cliente->updated_at ? $cliente->updated_at->diffForHumans() : '-' }}</div>
                </div>
            </div>

            <div class="stat-card flex items-center gap-3">
                <svg class="w-6 h-6 text-project-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M3 6h18" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 12h18" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 18h18" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <div>
                    <div class="text-sm text-gray-500">Teléfonos</div>
                    <div class="text-xl font-semibold text-gray-900">{{ $cliente->telefonos->count() }}</div>
                </div>
            </div>

            <div class="pt-2">
                <a href="{{ route('clients.index') }}" class="w-full inline-block btn-outline">Volver a lista</a>
            </div>
        </aside>
    </div>
</div>
