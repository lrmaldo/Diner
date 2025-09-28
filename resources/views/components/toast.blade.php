@once
<div id="global-toast-wrapper" wire:ignore>
    <div id="global-toast-container" class="fixed bottom-6 right-6 z-50 space-y-2"></div>

    <script>
        (function () {
            document.addEventListener('livewire:load', function () {
                console.log('[toasts] livewire:load fired, initializing toast listener');
                // Escuchar eventos globales emitidos por Livewire desde el servidor
                var toastHandler = function (data) {
                    data = data || {};
                    const container = document.getElementById('global-toast-container');
                    if (!container) return;

                    const toast = document.createElement('div');
                    toast.className = 'max-w-sm w-full bg-white shadow rounded-md p-3 border-l-4';
                    toast.style.borderColor = data.success ? '#10b981' : '#ef4444';
                    toast.innerHTML = `<div class="flex items-start gap-3"><div class="flex-1"><div class="font-medium">${data.success ? 'Éxito' : 'Error'}</div><div class="text-sm text-gray-600 mt-1">${data.message || ''}</div></div><button class="text-sm text-gray-400">✕</button></div>`;

                    container.appendChild(toast);
                    console.log('[toasts] showed toast', data);

                    const closeBtn = toast.querySelector('button');
                    if (closeBtn) {
                        closeBtn.addEventListener('click', function () { toast.remove(); });
                    }

                    setTimeout(function () { toast.remove(); }, 4000);
                };

                // Livewire global event bus (used by some server dispatch implementations)
                if (window.Livewire && typeof window.Livewire.on === 'function') {
                    window.Livewire.on('client-deleted', toastHandler);
                    // listen for miembroGuardado dispatched from server
                    window.Livewire.on('miembroGuardado', toastHandler);
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
                }
            });
        })();
    </script>
@endonce
