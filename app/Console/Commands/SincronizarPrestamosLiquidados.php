<?php

namespace App\Console\Commands;

use App\Models\Prestamo;
use Illuminate\Console\Command;

class SincronizarPrestamosLiquidados extends Command
{
    /**
     * @var string
     */
    protected $signature = 'prestamos:sincronizar-liquidados {--apply : Aplica los cambios. Sin esta opción solo muestra (dry-run).}';

    /**
     * @var string
     */
    protected $description = 'Marca como liquidados los préstamos que ya están pagados en su totalidad pero siguen en estado Entregado/Atrasado (residuo de redondeo entre calendario y caja).';

    public function handle(): int
    {
        $aplicar = (bool) $this->option('apply');

        $prestamos = Prestamo::whereIn('estado', ['Entregado', 'Atrasado'])
            ->with('pagos')
            ->get();

        $aMarcar = [];

        foreach ($prestamos as $prestamo) {
            try {
                if ($prestamo->estaLiquidado()) {
                    $aMarcar[] = $prestamo;
                }
            } catch (\Throwable $e) {
                $this->warn("Préstamo #{$prestamo->id}: no se pudo evaluar ({$e->getMessage()})");
            }
        }

        if (empty($aMarcar)) {
            $this->info('No hay préstamos pagados pendientes de marcar como liquidados.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Cliente', 'Estado actual', 'Saldo pendiente'],
            collect($aMarcar)->map(fn ($p) => [
                $p->id,
                $p->cliente->nombre_completo ?? ($p->cliente->nombres ?? 'N/A'),
                $p->estado,
                number_format($p->calcularSaldoPendiente(), 2),
            ])->all()
        );

        if (! $aplicar) {
            $this->warn(count($aMarcar).' préstamo(s) se marcarían como liquidados. Ejecuta con --apply para aplicar.');

            return self::SUCCESS;
        }

        foreach ($aMarcar as $prestamo) {
            $prestamo->estado = 'liquidado';
            $prestamo->save();
            $prestamo->registrarBitacora(
                'prestamo_liquidado',
                'Marcado como liquidado por sincronización (ya estaba pagado en su totalidad).'
            );
        }

        $this->info(count($aMarcar).' préstamo(s) marcados como liquidados.');

        return self::SUCCESS;
    }
}
