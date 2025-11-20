<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Proyecto extends Model
{
    use SoftDeletes;

    protected $table = 'proyectos';
    protected $primaryKey = 'cod_proy';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'cod_proy',
        'cod_cliente',
        'nombre_ubicacion',
        'direccion',
        'ciudad',
        'pais',
        'latitud',
        'longitud',
        'tipo_ubicacion',
        'fecha_inicio',
        'fecha_fin_estimada',
        'fecha_fin_real',
        'estado',
        'descripcion',
        'avance_financiero',
        'gasto_real',
        'rentabilidad',
        'responsable_proyecto',
        'supervisor_obra',
    ];

    protected $appends = [];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin_estimada' => 'date',
        'fecha_fin_real' => 'date',
        'avance_financiero' => 'decimal:2',
        'gasto_real' => 'decimal:2',
        'rentabilidad' => 'decimal:2',
    ];

    // Relaciones
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cod_cliente', 'cod_cliente');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'responsable_proyecto', 'cod_empleado');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'supervisor_obra', 'cod_empleado');
    }

    public function fases(): HasMany
    {
        return $this->hasMany(Fase::class, 'cod_proy', 'cod_proy');
    }

    public function empleados(): BelongsToMany
    {
        return $this->belongsToMany(Empleado::class, 'asignaciones_proyecto', 'cod_proy', 'cod_empleado')
                    ->withPivot([
                        'id_asignacion',
                        'fecha_inicio_asignacion',
                        'fecha_fin_asignacion',
                        'rol_en_proyecto',
                        'estado',
                    ])
                    ->withTimestamps();
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionProyecto::class, 'cod_proy', 'cod_proy');
    }

    public function kanbanBoard(): HasOne
    {
        return $this->hasOne(KanbanBoard::class, 'cod_proy', 'cod_proy');
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'cod_proy', 'cod_proy');
    }

    public function hitos(): HasMany
    {
        return $this->hasMany(Hito::class, 'cod_proy', 'cod_proy');
    }

    // Accessors para campos virtuales
    // Accessors/Mutators removed: ubicacion now mapped directly to proyectos table columns

    // No boot logic required: ubicaciones han sido integradas en la tabla proyectos
}
