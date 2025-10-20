<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionProyecto extends Model
{
    protected $table = 'asignaciones_proyecto';
    // La migración define la PK como 'id_asignacion'
    protected $primaryKey = 'id_asignacion';
    public $incrementing = false; 

    protected $fillable = [
        'id_asignacion',
        'cod_proy',
        'cod_empleado',
        'fecha_inicio_asignacion',
        'fecha_fin_asignacion',
        'rol_en_proyecto',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio_asignacion' => 'date',
        'fecha_fin_asignacion' => 'date',
    ];

   
    protected static function booted()
    {
        static::creating(function ($model) {
            // Fecha de inicio por defecto: hoy
            if (empty($model->fecha_inicio_asignacion)) {
                $model->fecha_inicio_asignacion = now();
            }

            if (empty($model->id_asignacion)) {
                $max = self::max('id_asignacion');
                $model->id_asignacion = $max ? ($max + 1) : 1;
            }
        });
    }

    // Relaciones
    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'cod_proy', 'cod_proy');
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'cod_empleado', 'cod_empleado');
    }

    // Scopes
    public function scopeActivos($query)
    { 
        return $query->where('estado', 'activo');
    }

    public function scopePorProyecto($query, $codProy)
    {
        return $query->where('cod_proy', $codProy);
    }

    public function scopePorEmpleado($query, $codEmpleado)
    {
        return $query->where('cod_empleado', $codEmpleado);
    }
}
