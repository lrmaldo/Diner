@once
<div id="global-toast-wrapper" wire:ignore>
    <div id="global-toast-container" class="fixed bottom-6 right-6 z-50 space-y-2"></div>

    <script>
        document.addEventListener('livewire:init', function () {
            console.log('[toasts] livewire:init fired, initializing toast listener');
            // Escuchar eventos globales emitidos por Livewire desde el servidor
            var toastHandler = function (data) {
                data = data || {};
                const container = document.getElementById('global-toast-container');
                if (!container) return;

                // Determine type and colors
                let type = data.type || (data.success ? 'success' : 'error');
                // Legacy support: if no type but success is explicit boolean
                if (!data.type && data.hasOwnProperty('success')) {
                    type = data.success ? 'success' : 'error';
                }
                
                let color = '#ef4444'; // default red
                let title = 'Error';

                if (type === 'success') {
                    color = '#10b981';
                    title = 'Éxito';
                } else if (type === 'warning') {
                    color = '#f59e0b'; // Amber-500
                    title = 'Atención';
                } else if (type === 'info') {
                    color = '#3b82f6'; // Blue-500
                    title = 'Información';
                }

                const toast = document.createElement('div');
                toast.className = 'max-w-sm w-full bg-white shadow-lg rounded-md p-3 border-l-4 pointer-events-auto transform transition-all duration-300 ease-in-out';
                toast.style.borderColor = color;
                
                toast.innerHTML = `<div class="flex items-start gap-3"><div class="flex-1"><div class="font-medium text-gray-900">${title}</div><div class="text-sm text-gray-600 mt-1">${data.message || ''}</div></div><button class="text-sm text-gray-400 hover:text-gray-600 focus:outline-none">✕</button></div>`;

                container.appendChild(toast);
                console.log('[toasts] showed toast', {type, message: data.message});

                const closeBtn = toast.querySelector('button');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function () { toast.remove(); });
                }

                setTimeout(function () { 
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 300);
                }, 5000);
            };

            // Livewire global event bus (used by some server dispatch implementations)
            if (window.Livewire && typeof window.Livewire.on === 'function') {
                window.Livewire.on('client-deleted', toastHandler);
                // listen for miembroGuardado dispatched from server
                window.Livewire.on('miembroGuardado', toastHandler);
                
                // Listen for generic alerts directly
                window.Livewire.on('alert', toastHandler);
                window.Livewire.on('toast', toastHandler);
            }

            // Fallback: native DOM CustomEvent dispatched on window
            if (typeof window.addEventListener === 'function') {
                window.addEventListener('client-deleted', function (e) {
                    var data = (e && e.detail) ? e.detail : (e || {});
                    toastHandler(data);
                });

                // also listen for miembroGuardado CustomEvent (fallback)
                window.addEventListener('miembroGuardado', function (e) {
                    var data = (e && e.detail) ? e.detail : (e || {});
                    toastHandler(data);
                });

                window.addEventListener('toast', function (e) {
                    var data = (e && e.detail) ? e.detail : (e || {});
                    // Sometimes Livewire wraps detail in an array if explicitly dispatched that way, but usually it's object
                    if (Array.isArray(data) && data.length > 0) data = data[0];
                    toastHandler(data);
                });
            }
        });
    </script>
@endonce
