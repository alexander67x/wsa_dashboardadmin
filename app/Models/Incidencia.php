<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incidencia extends Model
{
    protected $table = 'incidencias';
    protected $primaryKey = 'id_incidencia';

    protected $fillable = [
        'cod_proy',
        'id_tarea',
        'titulo',
        'descripcion',
        'tipo_incidencia',
        'severidad',
        'estado',
        'latitud',
        'longitud',
        'reportado_por',
        'asignado_a',
        'fecha_reportado',
        'fecha_resolucion',
        'solucion_implementada',
    ];

    protected $casts = [
        'fecha_reportado' => 'datetime',
        'fecha_resolucion' => 'datetime',
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'cod_proy', 'cod_proy');
    }

    public function tarea(): BelongsTo
    {
        return $this->belongsTo(Tarea::class, 'id_tarea', 'id_tarea');
    }

    public function reportadoPor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'reportado_por', 'cod_empleado');
    }

    public function asignadoA(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'asignado_a', 'cod_empleado');
    }

    public function evidencias(): HasMany
    {
        return $this->hasMany(IncidenciaEvidencia::class, 'id_incidencia', 'id_incidencia');
    }

    public function archivos(): BelongsToMany
    {
        return $this->belongsToMany(
            Archivo::class,
            'incidencia_evidencias',
            'id_incidencia',
            'archivo_id'
        )->withPivot('descripcion')
         ->orderBy('incidencia_evidencias.id');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(IncidenciaHistorial::class, 'id_incidencia', 'id_incidencia')
            ->orderBy('fecha_cambio', 'desc');
    }
}

