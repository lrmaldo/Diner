<div class="flex flex-col gap-6">
    <div class="flex flex-col items-center gap-4 text-center">
        <img src="{{ asset('img/logo.JPG') }}" alt="Logo Diner" class="h-20 w-auto rounded-lg shadow-md mb-2">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">
            Inicia sesión en Diner
        </h2>
        <p class="text-sm text-gray-600">
            Ingresa tus credenciales para acceder al sistema
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            label="Correo electrónico"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="usuario@ejemplo.com"
        />

        <!-- Password -->
        <div class="relative">
            <flux:input
                wire:model="password"
                label="Contraseña"
                type="password"
                required
                autocomplete="current-password"
                placeholder="Ingresa tu contraseña"
                viewable
            />

            @if (Route::has('password.request'))
                <flux:link class="absolute end-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                    ¿Olvidaste tu contraseña?
                </flux:link>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" label="Recordarme" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">Acceder</flux:button>
        </div>
    </form>
</div>
