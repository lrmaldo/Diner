<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrestamoBitacora extends Model
{
    use HasFactory;

    protected $fillable = [
        'prestamo_id',
        'user_id',
        'accion',
        'comentarios',
    ];

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
