<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <!-- Navegación Principal -->
            <nav class="flex-1 space-y-1 px-2 py-4">
                <!-- Dashboard -->
                {{-- <a href="{{ route('dashboard') }}"
                   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dashboard') ? 'bg-blue-100 text-blue-900 dark:bg-blue-900 dark:text-blue-100' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
                   wire:navigate>
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6a2 2 0 01-2 2H10a2 2 0 01-2-2V5z"></path>
                    </svg>
                    Dashboard
                </a> --}}

                <!-- Consultas -->
                <div x-data="{ open: {{ request()->routeIs('consultas.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full group flex items-center justify-between px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                        <div class="flex items-center">
                            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Consultas
                        </div>
                        <svg :class="{'rotate-90': open}" class="h-4 w-4 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    
                    <div x-show="open" class="space-y-1 pl-10" x-cloak>
                        <a href="{{ route('consultas.estados-cuenta') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('consultas.estados-cuenta') ? 'bg-blue-100 text-blue-900 dark:bg-blue-900 dark:text-blue-100' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
                           wire:navigate>
                            Estados de cuenta
                        </a>
                    </div>
                </div>

                <!-- Operaciones -->
                <div x-data="{ open: {{ request()->routeIs('operaciones.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full group flex items-center justify-between px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                        <div class="flex items-center">
                            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            Operaciones
                        </div>
                        <svg :class="{'rotate-90': open}" class="h-4 w-4 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    
                    <div x-show="open" class="space-y-1 pl-10" x-cloak>
                        <a href="{{ route('operaciones.aclaracion-pagos') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('operaciones.aclaracion-pagos') ? 'bg-blue-100 text-blue-900 dark:bg-blue-900 dark:text-blue-100' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
                           wire:navigate>
                            Aclaración de pagos
                        </a>
                    </div>
                </div>

                @can('ver prestamos')
                <!-- Préstamos -->
                <div class="space-y-1">
                    <!-- Menú Desplegable unificado para todos los roles -->
                    <div x-data="{ open: {{ request()->routeIs('prestamos.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" class="w-full group flex items-center justify-between px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                            <div class="flex items-center">
                                <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                Créditos
                            </div>
                            <svg :class="{'rotate-90': open}" class="h-4 w-4 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>

                        <div x-show="open" class="space-y-1 pl-10" x-cloak>
                            @can('crear prestamos')
                            <!-- Nuevo Préstamo -->
                            <a href="{{ route('prestamos.create') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('prestamos.create') ? 'bg-blue-100 text-blue-900 dark:bg-blue-900 dark:text-blue-100' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
                               wire:navigate>
                                Solicitar Crédito
                            </a>
                            @endcan

                            @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Asesor'))
                            <!-- En Comité -->
                            <a href="{{ route('prestamos.en-comite') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('prestamos.en-comite') ? 'bg-orange-100 text-orange-900 dark:bg-orange-900 dark:text-orange-100' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
                               wire:navigate>
                                En Comité
                            </a>

                            <!-- En Proceso -->
                            <a href="{{ route('prestamos.en-proceso') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('prestamos.en-proceso') ? 'bg-indigo-100 text-indigo-900 dark:bg-indigo-900 dark:text-indigo-100' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
                               wire:navigate>
                                En Proceso
                            </a>

                            <!-- Rechazados -->
                            <a href="{{ route('prestamos.rechazados') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('prestamos.rechazados') ? 'bg-red-100 text-red-900 dark:bg-red-900 dark:text-red-100' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
                               wire:navigate>
                                Rechazados
                            </a>
                            @endif

                            <!-- Préstamos Autorizados -->
                            <a href="{{ route('prestamos.autorizados') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('prestamos.autorizados') ? 'bg-green-100 text-green-900 dark:bg-green-900 dark:text-green-100' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
                               wire:navigate>
                                Autorizados
                            </a>
                        </div>
                    </div>
                </div>
                @endcan

                @if(auth()->user()->can('ver pagos') || auth()->user()->can('ver prestamos'))
                <!-- Caja -->
                <div x-data="{ open: {{ request()->routeIs('pagos.*') || request()->routeIs('caja.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full group flex items-center justify-between px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                        <div class="flex items-center">
                            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Caja
                        </div>
                        <svg :class="{'rotate-90': open}" class="h-4 w-4 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>

                    <div x-show="open" class="space-y-1 pl-10" x-cloak>
                        @can('ver pagos')
                        <!-- Cobros -->
                        <a href="{{ route('pagos.index') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('pagos.*') ? 'bg-blue-100 text-blue-900 dark:bg-blue-900 dark:text-blue-100' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
                           wire:navigate>
                            Cobros
                        </a>
                        @endcan

                        @can('ver prestamos')
                        <!-- Entrega de Créditos -->
                        <a href="{{ route('caja.entrega-credito') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('caja.entrega-credito') ? 'bg-blue-100 text-blue-900 dark:bg-blue-900 dark:text-blue-100' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
                           wire:navigate>
                            Entrega de Créditos
                        </a>

                        <!-- Sumar Capital -->
                        <a href="{{ route('caja.sumar-capital') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('caja.sumar-capital') ? 'bg-blue-100 text-blue-900 dark:bg-blue-900 dark:text-blue-100' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
                           wire:navigate>
                            <svg class="mr-3 h-6 w-6 text-gray-400 group-hover:text-gray-500 dark:text-gray-300 dark:group-hover:text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Sumar Capital (Capitalizar)
                        </a>

                        <!-- Devolución de Garantía -->
                        <a href="{{ route('caja.devolucion-garantia') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('caja.devolucion-garantia') ? 'bg-blue-100 text-blue-900 dark:bg-blue-900 dark:text-blue-100' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
                           wire:navigate>
                            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                            </svg>
                            Devolución de Garantía
                        </a>

                        @role('Administrador')
                        <!-- Arqueo de Caja -->
                        <a href="{{ route('caja.arqueo') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('caja.arqueo') ? 'bg-blue-100 text-blue-900 dark:bg-blue-900 dark:text-blue-100' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
                           wire:navigate>
                            <svg class="mr-3 h-6 w-6 text-gray-400 group-hover:text-gray-500 dark:text-gray-300 dark:group-hover:text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Arqueo de Caja
                        </a>
                        @endrole
                        @endcan
                    </div>
                </div>
                @endif

                @can('ver usuarios')
                <!-- Usuarios - Solo Administradores -->
                <a href="{{ route('users.index') }}"
                   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('users.*') ? 'bg-blue-100 text-blue-900 dark:bg-blue-900 dark:text-blue-100' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
                   wire:navigate>
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('users.*') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    Usuarios
                </a>
                @endcan
            </nav>

            <div class="flex-shrink-0 px-2 py-4">
                <!-- Información del rol del usuario -->
                <div class="mb-4 px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-md">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if(auth()->user()->hasRole('Administrador'))
                                <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                            @elseif(auth()->user()->hasRole('Cajero'))
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            @elseif(auth()->user()->hasRole('Asesor'))
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            @else
                                <div class="w-2 h-2 bg-gray-500 rounded-full"></div>
                            @endif
                        </div>
                        <div class="ml-2">
                            <p class="text-xs font-medium text-gray-900 dark:text-gray-100">
                                {{ auth()->user()->role ?? 'Usuario' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-200 dark:border-gray-700"></div>
            </div>

                <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>

                        @can('administrar sistema')
                        <flux:menu.item :href="route('settings.configurations')" icon="adjustments-horizontal" wire:navigate>Configuraciones</flux:menu.item>
                        <flux:menu.item :href="route('settings.holidays')" icon="calendar" wire:navigate>Días Festivos</flux:menu.item>
                        @endcan
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>

                        @can('administrar sistema')
                        <flux:menu.item :href="route('settings.configurations')" icon="adjustments-horizontal" wire:navigate>Configuraciones</flux:menu.item>
                        <flux:menu.item :href="route('settings.holidays')" icon="calendar" wire:navigate>Días Festivos</flux:menu.item>
                        @endcan
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
