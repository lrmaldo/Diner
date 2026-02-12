<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="bg-gray-100 dark:bg-zinc-900 flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="w-full max-w-md bg-white dark:bg-zinc-800 shadow-xl rounded-2xl p-8 border border-gray-100 dark:border-zinc-700">
                <div class="flex flex-col items-center gap-2 mb-6">
                    <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                        <img src="{{ asset('img/logo.JPG') }}" alt="Logo Diner" class="h-24 w-auto rounded-lg shadow-sm mb-2 hover:scale-105 transition-transform duration-300">
                    </a>
                </div>
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
            <div class="text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} Diner. Todos los derechos reservados.
            </div>
        </div>
        @fluxScripts
    </body>
</html>
