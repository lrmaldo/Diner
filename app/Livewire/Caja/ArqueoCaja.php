<?php

namespace App\Livewire\Caja;

use App\Models\Capitalizacion;
use App\Models\Pago;
use App\Models\Prestamo;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ArqueoCaja extends Component
{
    // Propiedades para Capitalizar (Modal)
    public $showCapitalizarModal = false;
    public $showSuccessModal = false;
    public $comentariosCapital = '';
    public $origenFondos = 'externo'; // externo, banco
    public $montoGuardado = 0;

    public $billetesCapital = [
        '1000' => 0, '500' => 0, '200' => 0, '100' => 0, '50' => 0, '20' => 0,
    ];

    public $monedasCapital = [
        '20' => 0, '10' => 0, '5' => 0, '2' => 0, '1' => 0, '0_5' => 0,
    ];

    public function mount()
    {
        if (!auth()->user()->hasRole('Administrador')) {
            abort(403, 'No tiene permisos para ver esta sección.');
        }
    }

    // Computed properties para el modal de Capitalizar
    public function getTotalBilletesCapitalProperty()
    {
        $total = 0;
        foreach ($this->billetesCapital as $denom => $qty) {
            $total += (float)$denom * (int)$qty;
        }
        return $total;
    }

    public function getTotalMonedasCapitalProperty()
    {
        $total = 0;
        foreach ($this->monedasCapital as $denom => $qty) {
            $val = $denom === '0_5' ? 0.5 : (float)$denom;
            $total += $val * (int)$qty;
        }
        return $total;
    }

    public function getTotalGeneralCapitalProperty()
    {
        return $this->totalBilletesCapital + $this->totalMonedasCapital;
    }

    public function abrirCapitalizar()
    {
        $this->reset(['billetesCapital', 'monedasCapital', 'comentariosCapital', 'origenFondos']);
        $this->origenFondos = 'externo';
        $this->showCapitalizarModal = true;
    }

    public function guardarCapital()
    {
        $this->validate([
            'origenFondos' => 'required|in:externo,banco',
        ]);

        if ($this->totalGeneralCapital <= 0) {
            $this->dispatch('toast', message: 'El monto total debe ser mayor a 0.', type: 'error');
            return;
        }

        $this->montoGuardado = $this->totalGeneralCapital;

        Capitalizacion::create([
            'monto' => $this->totalGeneralCapital,
            'origen_fondos' => $this->origenFondos,
            'desglose_billetes' => [
                'billetes' => $this->billetesCapital,
                'monedas' => $this->monedasCapital,
            ],
            'user_id' => auth()->id(),
            'comentarios' => $this->comentariosCapital,
        ]);

        $this->showCapitalizarModal = false;
        $this->showSuccessModal = true;
        
        // No es necesario emitir evento para recargar, Livewire refrescará el componente automáticamente 
        // y recalculará el Arqueo porque getDenominacionesProperty es una computed property que se evalúa al renderizar.
    }


    public function getDenominacionesProperty()
    {
        // Inicializar contadores en 0
        $caja = [
            '1000' => 0, '500' => 0, '200' => 0, '100' => 0, '50' => 0, '20' => 0,
            '10' => 0, '5' => 0, '2' => 0, '1' => 0, '0_5' => 0
        ];

        // Helper para sumar al arqueo
        $sumarAlArqueo = function ($desglose, $factor = 1) use (&$caja) {
            if (!$desglose) return;
            
            // Si el desglose viene como string JSON (algunos casos en BD antigua), decodificar
            if (is_string($desglose)) {
                $desglose = json_decode($desglose, true);
            }
            
            // Normalizar estructura: a veces viene directo ['1000' => 5], a veces ['billetes' => [...]]
            $billetes = $desglose['billetes'] ?? $desglose ?? [];
            $monedas = $desglose['monedas'] ?? [];
            
            // Sumar billetes
            foreach ($billetes as $denom => $cant) {
                // Filtrar claves no numéricas o anidadas incorrectas si la estructura varía
                if (isset($caja[(string)$denom]) && is_numeric($cant)) {
                    $caja[(string)$denom] += ((int)$cant * $factor);
                }
            }
            
            // Sumar monedas
            foreach ($monedas as $denom => $cant) {
                $denomStr = (string)$denom;
                // Normalizar clave antigua 0.5 a nueva 0_5
                if ($denomStr === '0.5') {
                    $denomStr = '0_5';
                }

                if (isset($caja[$denomStr]) && is_numeric($cant)) {
                    $caja[$denomStr] += ((int)$cant * $factor);
                }
            }
        };

        // 1. Sumar Capitalizaciones (Entradas)
        $capitalizaciones = Capitalizacion::all();
        foreach ($capitalizaciones as $cap) {
            $sumarAlArqueo($cap->desglose_billetes, 1);
        }

        // 2. Sumar Pagos Recibidos (Entradas)
        // Solo pagos con desglose_efectivo guardado
        $pagos = Pago::whereNotNull('desglose_efectivo')->get();
        
        // Mantener registro de transacciones procesadas para evitar duplicados en pagos grupales
        $processedUuids = [];
        $processedLegacyHashes = [];

        foreach ($pagos as $pago) {
            // Lógica para nuevas transacciones con UUID (Sistema robusto)
            if (!empty($pago->pago_uuid)) {
                if (in_array($pago->pago_uuid, $processedUuids)) {
                    continue; // Ya procesamos esta transacción física
                }
                $processedUuids[] = $pago->pago_uuid;
                
                // Sumar lo recibido
                $sumarAlArqueo($pago->desglose_efectivo, 1);
                
                // Restar el cambio entregado (si existe)
                if (!empty($pago->desglose_cambio)) {
                    $sumarAlArqueo($pago->desglose_cambio, -1);
                }
                
                continue;
            }

            // Lógica para pagos antiguos (Heurística: agrupar por préstamo, fecha exacta y desglose idéntico)
            // Esto corrige el problema de duplicidad en pagos grupales anteriores
            $hash = $pago->prestamo_id . '_' . ($pago->created_at ? $pago->created_at->format('Y-m-d H:i:s') : '') . '_' . json_encode($pago->desglose_efectivo);
            
            if (in_array($hash, $processedLegacyHashes)) {
                 continue; // Posible duplicado de registro por pago grupal
            }
            $processedLegacyHashes[] = $hash;
            
            $sumarAlArqueo($pago->desglose_efectivo, 1);
            // Pagos antiguos no guardaban desglose_cambio, así que no se resta nada
        }

        // 3. Restar Entregas de Crédito (Salidas)
        // Ya contamos con la columna desglose_entrega en la tabla prestamos.
        // Usamos whereNotNull('desglose_entrega') para incluir todos los préstamos que han salido,
        // independientemente de si su estado actual ha cambiado a 'activo', 'vencido' o 'pagado'.
        
        $prestamosEntregados = Prestamo::whereNotNull('desglose_entrega')->get();
            
        foreach ($prestamosEntregados as $p) {
             $sumarAlArqueo($p->desglose_entrega, -1); // Restar salida
        }

        return $caja;
    }
    
    public function getTotalSistemaProperty()
    {
        $total = 0;
        $denominaciones = $this->getDenominacionesProperty();
        
        foreach ($denominaciones as $denom => $cantidad) {
            $val = $denom === '0_5' ? 0.5 : (float)$denom;
            $total += $val * (int)$cantidad;
        }
        
        return $total;
    }

    public function getSaldoBancoProperty()
    {
        // Consideramos 'banco', 'Banco', 'transferencia', etc.
        $metodosBanco = ['banco', 'Banco', 'transferencia', 'Transferencia', 'deposito', 'Deposito'];

        $ingresosBanco = Pago::whereIn('metodo_pago', $metodosBanco)->sum('monto');
        $ingresosBancoMoratorio = Pago::whereIn('metodo_pago', $metodosBanco)->sum('moratorio_pagado');
        $egresosBanco = Capitalizacion::whereIn('origen_fondos', ['banco', 'Banco'])->sum('monto');
        
        return ($ingresosBanco + $ingresosBancoMoratorio) - $egresosBanco;
    }

    public function render()
    {
        return view('livewire.caja.arqueo-caja', [
            'denominaciones' => $this->getDenominacionesProperty(),
            'saldoBanco' => $this->getSaldoBancoProperty()
        ]);
    }
}
