<div class="p-4 max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold dark:text-white">Aclaración de pagos</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <form wire:submit.prevent="search" class="flex flex-col sm:flex-row items-end gap-4">
            <div class="w-full sm:w-auto flex-1 max-w-md">
                <label for="grupoSearch" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Grupo</label>
                <div class="relative">
                    <input type="text" 
                        wire:model="grupoSearch" 
                        id="grupoSearch" 
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Ingrese el grupo">
                </div>
            </div>
            
            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-transparent tex-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Buscar
            </button>
        </form>
    </div>
</div>
