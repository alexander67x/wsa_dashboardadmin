<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EjecucionSemanal extends Model
{
    protected $table = 'ejecucion_semanal';
    protected $primaryKey = 'id_ejecucion';
    public $timestamps = false;

    protected $fillable = [
        'id_plan',
        'actividades_ejecutadas',
        'porcentaje_cumplimiento',
        'avance_real_porcentaje',
        'causas_atraso',
        'estado',
        'reportado_por',
        'fecha_reporte',
        'aprobado_por',
        'fecha_aprobacion',
    ];

    protected $casts = [
        'porcentaje_cumplimiento' => 'float',
        'avance_real_porcentaje' => 'float',
        'fecha_reporte' => 'datetime',
        'fecha_aprobacion' => 'datetime',
    ];

    public function planificacion(): BelongsTo
    {
        return $this->belongsTo(PlanificacionSemanal::class, 'id_plan', 'id_plan');
    }
}
