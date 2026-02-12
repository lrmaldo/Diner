 <div class="flex flex-col gap-6">
    <div class="flex flex-col items-center gap-4 text-center">
        <img src="{{ asset('img/logo.JPG') }}" alt="Logo Diner" class="h-20 w-auto rounded-lg shadow-md mb-2">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">
            Recuperar contraseña
        </h2>
        <p class="text-sm text-gray-600">
            Ingresa tu correo para recibir un enlace de recuperación
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            label="Correo electrónico"
            type="email"
            required
            autofocus
            placeholder="usuario@ejemplo.com"
        />

        <flux:button variant="primary" type="submit" class="w-full">Enviar enlace de recuperación</flux:button>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600">
        <span>O regresar a</span>
        <flux:link :href="route('login')" wire:navigate>iniciar sesión</flux:link>
    </div>
</div>
