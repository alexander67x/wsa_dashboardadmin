<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tarea extends Model
{
    use SoftDeletes;

    protected $table = 'tareas';
    protected $primaryKey = 'id_tarea';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'cod_proy',
        'id_fase',
        'id_hito',
        'parent_id',
        'titulo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'duracion_dias',
        'prioridad',
        'estado',
        'supervisor_asignado',
        'wip_column_id',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'cod_proy', 'cod_proy');
    }

    public function fase(): BelongsTo
    {
        return $this->belongsTo(Fase::class, 'id_fase', 'id_fase');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Tarea::class, 'parent_id', 'id_tarea');
    }

    public function subtareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'parent_id', 'id_tarea');
    }

    public function hito(): BelongsTo
    {
        return $this->belongsTo(Hito::class, 'id_hito', 'id_hito');
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(KanbanColumn::class, 'wip_column_id', 'id_column');
    }

    public function responsables(): BelongsToMany
    {
        return $this->belongsToMany(Empleado::class, 'tarea_responsables', 'tarea_id', 'responsable_id')
            ->withTimestamps();
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'supervisor_asignado', 'cod_empleado');
    }

    public function reportes(): HasMany
    {
        return $this->hasMany(ReporteAvanceTarea::class, 'id_tarea', 'id_tarea');
    }
}
