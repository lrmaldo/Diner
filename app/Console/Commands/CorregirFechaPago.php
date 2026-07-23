<?php

namespace App\Console\Commands;

use App\Models\Pago;
use App\Models\Prestamo;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CorregirFechaPago extends Command
{
    /**
     * @var string
     */
    protected $signature = 'pagos:corregir-fecha {prestamo : ID del préstamo} {--pago= : ID del pago a corregir} {--fecha= : Nueva fecha de pago (YYYY-MM-DD)} {--apply : Aplica el cambio (sin esto es dry-run)}';

    /**
     * @var string
     */
    protected $description = 'Corrige la fecha de pago de un registro específico (ej. una aclaración capturada un día después). Muestra los pagos del préstamo si no se indica --pago.';

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

        // Sin --pago: listar todos los pagos para ubicar el correcto por cliente y N° de cuota.
        if (! $pagoId) {
            $this->info("Pagos del préstamo #{$prestamo->id}:");
            $this->table(
                ['pago_id', 'Cliente', 'Monto', 'Tipo', 'Método', 'N° cuota', 'Fecha de pago'],
                $prestamo->pagos->sortBy('id')->map(fn ($p) => [
                    $p->id,
                    $nombres[$p->cliente_id] ?? ('#'.$p->cliente_id),
                    number_format((float) $p->monto, 2),
                    $p->tipo_pago,
                    $p->metodo_pago,
                    $p->numero_pago ?? '-',
                    $p->fecha_pago ? Carbon::parse($p->fecha_pago)->format('Y-m-d') : '-',
                ])->all()
            );
            $this->line('Vuelve a ejecutar indicando --pago=<id> --fecha=<YYYY-MM-DD> para corregir.');

            return self::SUCCESS;
        }

        $pago = Pago::where('id', $pagoId)->where('prestamo_id', $prestamo->id)->first();

        if (! $pago) {
            $this->error("El pago #{$pagoId} no pertenece al préstamo #{$prestamo->id}.");

            return self::FAILURE;
        }

        if ($this->option('fecha') === null) {
            $this->error('Falta --fecha=<YYYY-MM-DD>.');

            return self::FAILURE;
        }

        try {
            $nuevaFecha = Carbon::createFromFormat('Y-m-d', $this->option('fecha'))->startOfDay();
        } catch (\Throwable $e) {
            $this->error('Fecha inválida. Usa el formato YYYY-MM-DD, por ejemplo 2026-05-19.');

            return self::FAILURE;
        }

        $fechaActual = $pago->fecha_pago ? Carbon::parse($pago->fecha_pago) : null;

        $this->info("Pago #{$pago->id} — {$nombres[$pago->cliente_id]} (préstamo #{$prestamo->id})");
        $this->line("  Cuota N°: ".($pago->numero_pago ?? '-')." | Monto: $".number_format((float) $pago->monto, 2)." | Tipo: {$pago->tipo_pago} | Método: {$pago->metodo_pago}");
        $this->line('  Fecha actual:  '.($fechaActual ? $fechaActual->format('Y-m-d') : '(sin fecha)'));
        $this->line('  Fecha nueva:   '.$nuevaFecha->format('Y-m-d'));

        if (! $this->option('apply')) {
            $this->warn('DRY-RUN: no se aplicó nada. Agrega --apply para guardar el cambio.');

            return self::SUCCESS;
        }

        $pago->fecha_pago = $nuevaFecha;
        $pago->save();

        $prestamo->registrarBitacora(
            'pago_corregido',
            "Corrección de fecha del pago #{$pago->id} ({$nombres[$pago->cliente_id]}, cuota ".($pago->numero_pago ?? '-')."): de ".($fechaActual ? $fechaActual->format('Y-m-d') : 'sin fecha').' a '.$nuevaFecha->format('Y-m-d').'.'
        );

        $this->info('Listo. Fecha corregida y registrada en la bitácora del préstamo.');

        return self::SUCCESS;
    }
}
