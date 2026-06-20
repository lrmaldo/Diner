<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Egreso extends Model
{
    use HasFactory;

    protected $fillable = [
        'origen',
        'monto',
        'descripcion',
        'fecha',
        'denominaciones',
        'user_id',
    ];

    protected $casts = [
        'denominaciones' => 'array',
        'fecha' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
