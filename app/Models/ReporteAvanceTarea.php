<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReporteAvanceTarea extends Model
{
    protected $table = 'reportes_avance_tarea';
    protected $primaryKey = 'id_reporte';

    protected $fillable = [
        'id_tarea',
        'cod_proy',
        'titulo',
        'descripcion',
        'fecha_reporte',
        'dificultades_encontradas',
        'materiales_utilizados',
        'registrado_por',
        'estado',
        'observaciones_supervisor',
        'fecha_aprobacion',
        'aprobado_por',
    ];

    protected $casts = [
        'fecha_reporte' => 'date',
        'fecha_aprobacion' => 'datetime',
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'cod_proy', 'cod_proy');
    }

    public function tarea(): BelongsTo
    {
        return $this->belongsTo(Tarea::class, 'id_tarea', 'id_tarea');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'registrado_por', 'cod_empleado');
    }

    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'aprobado_por', 'cod_empleado');
    }

    public function archivos(): BelongsToMany
    {
        return $this->belongsToMany(
            Archivo::class,
            'reporte_archivos',
            'id_reporte',
            'archivo_id'
        )->withPivot('es_foto_principal')
         ->orderBy('reporte_archivos.id');
    }

    public function evidencias(): HasMany
    {
        return $this->hasMany(ReporteArchivo::class, 'id_reporte', 'id_reporte');
    }

    public function materiales(): HasMany
    {
        return $this->hasMany(ReporteMaterial::class, 'id_reporte', 'id_reporte');
    }
}








