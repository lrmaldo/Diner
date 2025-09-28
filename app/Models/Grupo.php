<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupos';

    protected $fillable = ['nombre', 'descripcion'];

    public function clientes()
    {
        return $this->belongsToMany(Cliente::class, 'grupo_cliente', 'grupo_id', 'cliente_id');
    }

    public function prestamos()
    {
        return $this->morphMany(Prestamo::class, 'prestamoable');
    }
}
