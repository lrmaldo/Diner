<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capitalizacion extends Model
{
    use HasFactory;

    protected $table = 'capitalizaciones';

    protected $fillable = [
        'monto',
        'origen_fondos',
        'desglose_billetes',
        'user_id',
        'comentarios',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'desglose_billetes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
