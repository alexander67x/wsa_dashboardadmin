<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanificacionSemanal extends Model
{
    protected $table = 'planificacion_semanal';
    protected $primaryKey = 'id_plan';
    public $timestamps = false;

    protected $fillable = [
        'cod_proy',
        'semana',
        'año',
        'actividades_planificadas',
        'hitos_esperados',
        'recursos_requeridos',
        'avance_esperado_porcentaje',
        'estado',
        'planificado_por',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'avance_esperado_porcentaje' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'cod_proy', 'cod_proy');
    }

    public function ejecuciones(): HasMany
    {
        return $this->hasMany(EjecucionSemanal::class, 'id_plan', 'id_plan');
    }
}
