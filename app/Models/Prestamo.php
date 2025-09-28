<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Prestamo extends Model
{
    use HasFactory;

    protected $table = 'prestamos';

    protected $fillable = [
        'folio', 'producto', 'monto_total', 'plazo', 'periodicidad', 'periodo_pago', 'dia_pago', 'fecha_entrega', 'fecha_primer_pago', 'tasa_interes', 'estado', 'autorizado_por', 'cliente_id', 'grupo_id'
    ];

    protected $casts = [
        'fecha_entrega' => 'date',
        'fecha_primer_pago' => 'date',
        'monto_total' => 'decimal:2',
        'tasa_interes' => 'decimal:4',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->folio)) {
                // generar folio único: PRE-YYYYMMDD-NNNN
                $date = date('Ymd');
                $prefix = "PRE-{$date}-";
                $last = self::where('folio', 'like', "{$prefix}%")->orderByDesc('id')->first();
                $num = 1;
                if ($last && preg_match('/-(\d+)$/', $last->folio, $m)) {
                    $num = intval($m[1]) + 1;
                }
                $model->folio = $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function prestamoable()
    {
        return $this->morphTo();
    }

    // relación 1 a 1 con cliente si producto == 'individual'
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // relación muchos a muchos para prestamos grupales
    public function clientes()
    {
        return $this->belongsToMany(Cliente::class, 'cliente_prestamo', 'prestamo_id', 'cliente_id')
            ->withPivot('monto_solicitado')
            ->withTimestamps();
    }

    public function autorizador()
    {
        return $this->belongsTo(User::class, 'autorizado_por');
    }

    public function autorizar(User $user): void
    {
        $this->estado = 'autorizado';
        $this->autorizado_por = $user->id;
        $this->save();

        // Aquí se podría disparar la lógica de descuento de capital o eventos.
    }

    public function rechazar(User $user): void
    {
        $this->estado = 'rechazado';
        $this->autorizado_por = $user->id;
        $this->save();
    }
}
