<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'pago_uuid',
        'prestamo_id',
        'cliente_id',
        'registrado_por',
        'monto',
        'fecha_pago',
        'tipo_pago',
        'numero_pago',
        'saldo_anterior',
        'saldo_nuevo',
        'interes_pagado',
        'capital_pagado',
        'moratorio_pagado',
        'notas',
        'desglose_efectivo',
        'desglose_cambio',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'monto' => 'decimal:2',
        'saldo_anterior' => 'decimal:2',
        'saldo_nuevo' => 'decimal:2',
        'interes_pagado' => 'decimal:2',
        'capital_pagado' => 'decimal:2',
        'moratorio_pagado' => 'decimal:2',
        'desglose_efectivo' => 'array',
        'desglose_cambio' => 'array',
    ];

    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class, 'prestamo_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    /**
     * Calcular el total de efectivo recibido basado en el desglose
     */
    public function calcularTotalEfectivo(): float
    {
        if (empty($this->desglose_efectivo)) {
            return 0;
        }

        $total = 0;
        $billetes = $this->desglose_efectivo['billetes'] ?? [];
        $monedas = $this->desglose_efectivo['monedas'] ?? [];

        foreach ($billetes as $denominacion => $cantidad) {
            $total += (float) $denominacion * (int) $cantidad;
        }

        foreach ($monedas as $denominacion => $cantidad) {
            $total += (float) $denominacion * (int) $cantidad;
        }

        return $total;
    }
}
