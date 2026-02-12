<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'year',
        'is_recurring',
        'type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'year' => 'integer',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Verificar si una fecha es día feriado
     */
    public static function isHoliday(Carbon $date): bool
    {
        return self::where('date', $date->format('Y-m-d'))
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Obtener el siguiente día hábil (excluyendo feriados y fines de semana)
     */
    public static function getNextBusinessDay(Carbon $date): Carbon
    {
        $nextDay = $date->copy();

        do {
            $nextDay->addDay();
        } while (
            $nextDay->isWeekend() ||
            self::isHoliday($nextDay)
        );

        return $nextDay;
    }

    /**
     * Obtener todos los feriados de un año
     */
    public static function getForYear(int $year): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('year', $year)
            ->where('is_active', true)
            ->orderBy('date')
            ->get();
    }

    /**
     * Crear feriados recurrentes para un año
     */
    public static function createRecurringForYear(int $year): void
    {
        // 1. Intentar copiar feriados recurrentes existentes
        $recurringHolidays = self::where('is_recurring', true)->get();

        foreach ($recurringHolidays as $holiday) {
            $newDate = Carbon::parse($holiday->date)->setYear($year);

            self::updateOrCreate(
                [
                    'date' => $newDate->format('Y-m-d'),
                ],
                [
                    'year' => $year,
                    'name' => $holiday->name,
                    'is_recurring' => true,
                    'type' => $holiday->type,
                    'description' => $holiday->description,
                    'is_active' => true,
                ]
            );
        }

        // 2. Asegurar que los días festivos obligatorios de México existan
        // Se usa firstOrCreate para no duplicar si ya fueron copiados o creados manualmente
        $defaults = [
            ['name' => 'Año Nuevo', 'date' => "$year-01-01"],
            ['name' => 'Día de la Constitución', 'date' => "$year-02-05"],
            ['name' => 'Natalicio de Benito Juárez', 'date' => "$year-03-21"],
            ['name' => 'Día del Trabajo', 'date' => "$year-05-01"],
            ['name' => 'Día de la Independencia', 'date' => "$year-09-16"],
            ['name' => 'Revolución Mexicana', 'date' => "$year-11-20"],
            ['name' => 'Navidad', 'date' => "$year-12-25"],
        ];

        foreach ($defaults as $def) {
            self::firstOrCreate(
                ['date' => $def['date']],
                [
                    'name' => $def['name'],
                    'year' => $year,
                    'is_recurring' => true,
                    'type' => 'national',
                    'description' => 'Festivo oficial',
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Scopes para filtrar
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
