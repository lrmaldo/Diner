<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'apellido_paterno',
        'apellido_materno',
        'nombres',
        'curp',
        'email',
        'pais_nacimiento',
        'nombre_conyuge',
        'calle_numero',
        'referencia_domiciliaria',
        'estado_civil',
        'dependientes_economicos',
        'nombre_aval',
        'actividad_productiva',
        'anios_experiencia',
        'ingreso_mensual',
        'gasto_mensual_familiar',
        'credito_solicitado',
        'estado',
        'municipio',
        'colonia',
        'codigo_postal',
    ];

    protected $casts = [
        'dependientes_economicos' => 'integer',
        'anios_experiencia' => 'integer',
        'ingreso_mensual' => 'decimal:2',
        'gasto_mensual_familiar' => 'decimal:2',
        'credito_solicitado' => 'decimal:2',
    ];

    public function telefonos()
    {
        return $this->hasMany(Telefono::class, 'cliente_id');
    }

    public function prestamos()
    {
        return $this->morphMany(Prestamo::class, 'prestamoable');
    }

    public function prestamosAsignados()
    {
        return $this->belongsToMany(Prestamo::class, 'cliente_prestamo', 'cliente_id', 'prestamo_id')
            ->withPivot('monto_solicitado', 'monto_sugerido', 'monto_autorizado')
            ->withTimestamps();
    }

    public function getNombreCompletoAttribute()
    {
        return trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}");
    }
}
