<div class="flex flex-col gap-6">
    <div class="flex flex-col items-center gap-4 text-center">
        <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 mb-2">
            ¡Bienvenido!
        </h2>
        <p class="text-sm text-gray-500">
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
        <div class="space-y-2">
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
                <div class="flex justify-end">
                    <flux:link class="text-sm font-medium text-red-600 hover:text-red-500" :href="route('password.request')" wire:navigate>
                        ¿Olvidaste tu contraseña?
                    </flux:link>
                </div>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" label="Recordarme" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">Acceder</flux:button>
        </div>
    </form>
</div>
