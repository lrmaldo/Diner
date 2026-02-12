<x-layouts.app :title="__('Dashboard')">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-xl sm:rounded-lg border border-gray-100 dark:border-zinc-800">
                <div class="p-8 md:p-12 text-gray-900 dark:text-gray-100 flex flex-col items-center justify-center min-h-[70vh] text-center">
                    
                    {{-- Logo --}}
                    <div class="mb-10 transform hover:scale-105 transition-transform duration-500">
                        <img src="{{ asset('img/logo.JPG') }}" alt="Diner Logo" class="h-48 w-auto rounded-xl shadow-2xl">
                    </div>
                    
                    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-800 dark:text-white mb-16 tracking-tight">
                        Bienvenido a <span class="text-red-600">Diner</span>
                    </h1>

                    <div class="grid md:grid-cols-2 gap-8 md:gap-16 max-w-5xl w-full">
                        <!-- Misión -->
                        <div class="group flex flex-col items-center p-8 bg-gray-50 dark:bg-zinc-800/50 rounded-2xl border border-gray-200 dark:border-zinc-700 shadow-sm hover:shadow-xl transition-all duration-300">
                            <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-full mb-6 group-hover:bg-red-200 dark:group-hover:bg-red-800/40 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4 border-b-2 border-red-500 pb-2">
                                Misión
                            </h2>
                            <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed font-medium">
                                "Contribuimos para mejorar la calidad de vida de la sociedad, ofreciendo productos financieros a tu medida."
                            </p>
                        </div>

                        <!-- Visión -->
                        <div class="group flex flex-col items-center p-8 bg-gray-50 dark:bg-zinc-800/50 rounded-2xl border border-gray-200 dark:border-zinc-700 shadow-sm hover:shadow-xl transition-all duration-300">
                            <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full mb-6 group-hover:bg-blue-200 dark:group-hover:bg-blue-800/40 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4 border-b-2 border-blue-500 pb-2">
                                Visión
                            </h2>
                            <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed font-medium">
                                "Posicionarnos en la mente del consumidor, ofreciendo un servicio de calidad."
                            </p>
                        </div>
                    </div>

                    <div class="mt-16 text-sm text-gray-400">
                        &copy; {{ date('Y') }} Sistema de Gestión de Préstamos Diner.
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

