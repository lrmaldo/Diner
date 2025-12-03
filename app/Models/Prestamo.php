<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $table = 'prestamos';

    protected $fillable = [
        'folio', 'producto', 'monto_total', 'monto_sugerido', 'plazo', 'periodicidad', 'periodo_pago', 'dia_pago', 'fecha_entrega', 'fecha_primer_pago', 'tasa_interes', 'garantia', 'estado', 'autorizado_por', 'cliente_id', 'grupo_id', 'representante_id', 'asesor_id', 'motivo_rechazo', 'comentarios_comite',
    ];

    protected $casts = [
        'fecha_entrega' => 'date',
        'fecha_primer_pago' => 'date',
        'monto_total' => 'decimal:2',
        'monto_sugerido' => 'decimal:2',
        'tasa_interes' => 'decimal:4',
        'garantia' => 'decimal:2',
    ];

    // Generador de folio deshabilitado - ahora usamos el ID directamente
    // protected static function booted()
    // {
    //     static::creating(function ($model) {
    //         if (empty($model->folio)) {
    //             // generar folio único: PRE-YYYYMMDD-NNNN
    //             $date = date('Ymd');
    //             $prefix = "PRE-{$date}-";
    //             $last = self::where('folio', 'like', "{$prefix}%")->orderByDesc('id')->first();
    //             $num = 1;
    //             if ($last && preg_match('/-(\d+)$/', $last->folio, $m)) {
    //                 $num = intval($m[1]) + 1;
    //             }
    //             $model->folio = $prefix.str_pad($num, 4, '0', STR_PAD_LEFT);
    //         }
    //     });
    // }

    public function prestamoable()
    {
        return $this->morphTo();
    }

    // relación 1 a 1 con cliente si producto == 'individual'
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Representante del préstamo: cliente designado para préstamos grupales o el mismo cliente en individuales
     */
    public function representante()
    {
        return $this->belongsTo(Cliente::class, 'representante_id');
    }

    /**
     * Grupo al que pertenece el préstamo (solo para préstamos grupales)
     */
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    // relación muchos a muchos para prestamos grupales
    public function clientes()
    {
        return $this->belongsToMany(Cliente::class, 'cliente_prestamo', 'prestamo_id', 'cliente_id')
            ->withPivot('monto_solicitado', 'monto_sugerido', 'monto_autorizado')
            ->withTimestamps();
    }

    public function autorizador()
    {
        return $this->belongsTo(User::class, 'autorizado_por');
    }

    /**
     * Asesor asignado al préstamo
     */
    public function asesor()
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    /**
     * Calcular el total solicitado sumando desde la tabla pivot
     */
    public function calcularTotalSolicitado(): float
    {
        if ($this->producto === 'grupal' && $this->clientes && $this->clientes->count() > 0) {
            return $this->clientes->sum(function ($cliente) {
                return (float) ($cliente->pivot->monto_solicitado ?? 0);
            });
        }

        // Para individuales, usar el monto_total o buscar en pivot
        if ($this->cliente_id && $this->clientes && $this->clientes->count() > 0) {
            $cliente = $this->clientes->first();

            return (float) ($cliente->pivot->monto_solicitado ?? $this->monto_total ?? 0);
        }

        return (float) ($this->monto_total ?? 0);
    }

    /**
     * Calcular el total autorizado sumando desde la tabla pivot
     */
    public function calcularTotalAutorizado(): float
    {
        $total = 0;

        if ($this->clientes && $this->clientes->count() > 0) {
            $total = $this->clientes->sum(function ($cliente) {
                return (float) ($cliente->pivot->monto_autorizado ?? 0);
            });
        }

        // Si está autorizado pero el total es 0, asumir que es el total solicitado (migración/fallback)
        if ($this->estado === 'autorizado' && $total <= 0) {
            return $this->calcularTotalSolicitado();
        }

        return $total;
    }

    public function autorizar(User $user): void
    {
        $this->estado = 'autorizado';
        $this->autorizado_por = $user->id;

        // Primero, asegurar que todos los clientes tengan monto_autorizado
        // Si es null, usar el monto_solicitado como autorizado
        $this->load('clientes'); // Recargar para asegurar datos frescos de la BD
        foreach ($this->clientes as $cliente) {
            if ($cliente->pivot->monto_autorizado === null) {
                $montoSolicitado = $cliente->pivot->monto_solicitado ?? 0;
                $this->clientes()->updateExistingPivot($cliente->id, [
                    'monto_autorizado' => $montoSolicitado,
                ]);
            }
        }

        // Recargar la relación para obtener los datos actualizados
        $this->load('clientes');

        // Calcular el monto total autorizado después de la actualización
        $totalAutorizado = $this->calcularTotalAutorizado();

        // Actualizar monto_total del préstamo
        if ($totalAutorizado > 0) {
            $this->monto_total = $totalAutorizado;
        }

        $this->save();

        // Aquí se podría disparar la lógica de descuento de capital o eventos.
    }

    public function rechazar(User $user, ?string $motivo = null): void
    {
        $this->estado = 'rechazado';
        $this->autorizado_por = $user->id;
        $this->motivo_rechazo = $motivo;
        $this->save();
    }

    public function bitacora()
    {
        return $this->hasMany(PrestamoBitacora::class)->orderByDesc('created_at');
    }

    public function registrarBitacora(string $accion, ?string $comentarios = null): void
    {
        $this->bitacora()->create([
            'user_id' => auth()->id(),
            'accion' => $accion,
            'comentarios' => $comentarios,
        ]);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class)->orderBy('fecha_pago');
    }

    /**
     * Calcular el total pagado sumando todos los pagos
     */
    public function calcularTotalPagado(): float
    {
        return $this->pagos()->sum('monto');
    }

    /**
     * Calcular el saldo pendiente
     */
    public function calcularSaldoPendiente(): float
    {
        return $this->monto_total - $this->calcularTotalPagado();
    }
}
