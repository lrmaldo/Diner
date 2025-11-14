<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'category',
        'editable',
    ];

    protected $casts = [
        'editable' => 'boolean',
    ];

    /**
     * Obtener el valor parseado según el tipo
     */
    public function getParsedValueAttribute()
    {
        return match ($this->type) {
            'decimal' => (float) $this->value,
            'integer' => (int) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    /**
     * Método estático para obtener una configuración por clave
     */
    public static function get(string $key, $default = null)
    {
        $config = self::where('key', $key)->first();
        return $config ? $config->parsed_value : $default;
    }

    /**
     * Método estático para establecer una configuración
     */
    public static function set(string $key, $value, string $type = 'string'): void
    {
        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'type' => $type,
            ]
        );
    }

    /**
     * Scopes para filtrar por categoría
     */
    public function scopeFinancial($query)
    {
        return $query->where('category', 'financial');
    }

    public function scopeGeneral($query)
    {
        return $query->where('category', 'general');
    }

    public function scopeEditable($query)
    {
        return $query->where('editable', true);
    }
}
