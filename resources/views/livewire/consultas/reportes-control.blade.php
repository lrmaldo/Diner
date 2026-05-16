<div class="max-w-2xl mx-auto py-8">
    <div class="bg-white border border-gray-300">
        <div class="bg-red-600 text-white font-bold text-center py-2 flex justify-between items-center px-4">
            <div class="flex-grow text-center text-lg">Parámetros de consulta</div>
            <svg class="h-5 w-5 text-gray-400 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
        <div class="p-0 border-t border-gray-300">
            @if (session()->has('message'))
                <div class="m-4 p-3 bg-green-100 text-green-700 rounded border border-green-200 text-center">
                    {{ session('message') }}
                </div>
            @endif
            
            <form wire:submit="generar" class="p-0">
                <select wire:model="parametro" class="block w-full py-2 px-3 border-0 bg-white focus:outline-none focus:ring-0 sm:text-sm text-center">
                    @foreach($opciones as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                
                <div class="flex justify-center my-6">
                    <button type="submit" class="inline-flex justify-center py-2 px-8 border border-red-700 shadow-sm text-sm font-bold rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Generar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
