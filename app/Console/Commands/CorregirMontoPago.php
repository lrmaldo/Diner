<?php

namespace App\Console\Commands;

use App\Models\Pago;
use App\Models\Prestamo;
use Illuminate\Console\Command;

class CorregirMontoPago extends Command
{
    /**
     * @var string
     */
    protected $signature = 'pagos:corregir-monto {prestamo : ID del préstamo} {--pago= : ID del pago a corregir} {--monto= : Nuevo monto total del pago} {--apply : Aplica el cambio (sin esto es dry-run)}';

    /**
     * @var string
     */
    protected $description = 'Corrige el monto de un pago específico (ej. una aclaración capturada con un dígito de más). Muestra los pagos del préstamo si no se indica --pago.';

    public function handle(): int
    {
        $prestamo = Prestamo::with(['pagos', 'clientes', 'cliente'])->find($this->argument('prestamo'));

        if (! $prestamo) {
            $this->error('No existe el préstamo #'.$this->argument('prestamo'));

            return self::FAILURE;
        }

        $nombres = [];
        foreach ($prestamo->clientes as $c) {
            $nombres[$c->id] = trim($c->nombres.' '.$c->apellido_paterno.' '.$c->apellido_materno);
        }
        if ($prestamo->cliente) {
            $nombres[$prestamo->cliente->id] = trim($prestamo->cliente->nombres.' '.$prestamo->cliente->apellido_paterno.' '.$prestamo->cliente->apellido_materno);
        }

        $pagoId = $this->option('pago');

        // Sin --pago: listar todos los pagos para ubicar el correcto.
        if (! $pagoId) {
            $this->info("Pagos del préstamo #{$prestamo->id}:");
            $this->table(
                ['pago_id', 'Cliente', 'Monto', 'Moratorio', 'Tipo', 'Método', 'N° cuota', 'Fecha'],
                $prestamo->pagos->sortBy('id')->map(fn ($p) => [
                    $p->id,
                    $nombres[$p->cliente_id] ?? ('#'.$p->cliente_id),
                    number_format((float) $p->monto, 2),
                    number_format((float) $p->moratorio_pagado, 2),
                    $p->tipo_pago,
                    $p->metodo_pago,
                    $p->numero_pago ?? '-',
                    $p->fecha_pago,
                ])->all()
            );
            $this->line('Vuelve a ejecutar indicando --pago=<id> --monto=<nuevo_monto> para corregir.');

            return self::SUCCESS;
        }

        $pago = Pago::where('id', $pagoId)->where('prestamo_id', $prestamo->id)->first();

        if (! $pago) {
            $this->error("El pago #{$pagoId} no pertenece al préstamo #{$prestamo->id}.");

            return self::FAILURE;
        }

        if ($this->option('monto') === null) {
            $this->error('Falta --monto=<nuevo_monto>.');

            return self::FAILURE;
        }

        $nuevoMonto = (float) $this->option('monto');
        $montoActual = (float) $pago->monto;
        $moratorio = (float) $pago->moratorio_pagado;

        $this->info("Pago #{$pago->id} — {$nombres[$pago->cliente_id]} (préstamo #{$prestamo->id})");
        $this->line("  Tipo: {$pago->tipo_pago} | Método: {$pago->metodo_pago} | Fecha: {$pago->fecha_pago}");
        $this->line('  Monto actual:  $'.number_format($montoActual, 2).($moratorio > 0 ? ' (incluye $'.number_format($moratorio, 2).' de moratorio)' : ''));
        $this->line('  Monto nuevo:   $'.number_format($nuevoMonto, 2));

        if ($moratorio > 0 && $nuevoMonto < $moratorio) {
            $this->warn('  ¡Atención! El nuevo monto es menor que el moratorio registrado. Revisa antes de aplicar.');
        }

        if (! $this->option('apply')) {
            $this->warn('DRY-RUN: no se aplicó nada. Agrega --apply para guardar el cambio.');

            return self::SUCCESS;
        }

        $pago->monto = $nuevoMonto;
        $pago->save();

        $prestamo->registrarBitacora(
            'pago_corregido',
            "Corrección de monto del pago #{$pago->id} ({$nombres[$pago->cliente_id]}): de $".number_format($montoActual, 2).' a $'.number_format($nuevoMonto, 2).'.'
        );

        $this->info('Listo. Monto corregido y registrado en la bitácora del préstamo.');

        return self::SUCCESS;
    }
}
