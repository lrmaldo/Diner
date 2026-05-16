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

    {{-- Paletas de Información --}}
    <div class="mt-8 flex flex-row flex-wrap justify-between items-stretch gap-4 p-4 bg-gray-50 rounded-xl border border-gray-300 mx-auto" style="max-width: 900px;">
        
        {{-- Paleta: Clientes (Rosa) --}}
        <div class="flex-1 bg-pink-500 rounded-lg text-white p-4 shadow min-w-32">
            <h3 class="font-bold text-center border-b border-pink-400 pb-2 mb-2">Clientes</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['actual'] }}:</span>
                    <span class="font-bold">{{ number_format($this->datosClientes['al_dia']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['mes1'] }}:</span>
                    <span class="font-bold">{{ number_format($this->datosClientes['mes1']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['mes2'] }}:</span>
                    <span class="font-bold">{{ number_format($this->datosClientes['mes2']) }}</span>
                </div>
            </div>
        </div>

        {{-- Paleta: Colocación (Rojo) --}}
        <div class="flex-1 bg-red-600 rounded-lg text-white p-4 shadow min-w-32">
            <h3 class="font-bold text-center border-b border-red-500 pb-2 mb-2">Colocación</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['actual'] }}:</span>
                    <span class="font-bold">${{ number_format($this->datosColocacion['al_dia'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['mes1'] }}:</span>
                    <span class="font-bold">${{ number_format($this->datosColocacion['mes1'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['mes2'] }}:</span>
                    <span class="font-bold">${{ number_format($this->datosColocacion['mes2'], 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Paleta: Fidelización (Amarillo Verde) --}}
        <div class="flex-1 bg-yellow-400 rounded-lg text-white p-4 shadow min-w-32">
            <h3 class="font-bold text-center border-b border-yellow-300 pb-2 mb-2 text-shadow-sm">Fidelización</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['actual'] }}:</span>
                    <span class="font-bold">-</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['mes1'] }}:</span>
                    <span class="font-bold">-</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['mes2'] }}:</span>
                    <span class="font-bold">-</span>
                </div>
            </div>
        </div>

        {{-- Paleta: Exigible (Verde) --}}
        <div class="flex-1 bg-green-500 rounded-lg text-white p-4 shadow min-w-32">
            <h3 class="font-bold text-center border-b border-green-400 pb-2 mb-2">Exigible</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['actual'] }}:</span>
                    <span class="font-bold">-</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['mes1'] }}:</span>
                    <span class="font-bold">-</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ $this->mesesNombres['mes2'] }}:</span>
                    <span class="font-bold">-</span>
                </div>
            </div>
        </div>

        {{-- Paleta: Monto Activo (Celeste) --}}
        <div class="flex-1 bg-cyan-400 rounded-lg text-white p-4 shadow flex flex-col justify-center min-w-32">
            <h3 class="font-bold text-center border-b border-cyan-300 pb-2 mb-4">Monto activo:</h3>
            <div class="text-center text-xl font-bold">
                $ -
            </div>
        </div>
    </div>
</div>
