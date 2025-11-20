<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hito extends Model
{
    use SoftDeletes;

    protected $table = 'hitos';

    protected $primaryKey = 'id_hito';

    public $incrementing = true;

    protected $fillable = [
        'cod_proy',
        'id_fase',
        'titulo',
        'descripcion',
        'fecha_final_hito',
        'fecha_hito',
        'tipo',
        'es_critico',
        'estado',
        'creado_por',
    ];

    protected $casts = [
        'fecha_final_hito' => 'date',
        'fecha_hito' => 'date',
        'es_critico' => 'boolean',
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'cod_proy', 'cod_proy');
    }

    public function fase(): BelongsTo
    {
        return $this->belongsTo(Fase::class, 'id_fase', 'id_fase');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'creado_por', 'cod_empleado');
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'id_hito', 'id_hito');
    }
}
