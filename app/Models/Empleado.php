<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Empleado extends Model
{
    use SoftDeletes;

    protected $table = 'empleados';
    protected $primaryKey = 'cod_empleado';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nombre_completo',
        'cargo',
        'departamento',
        'email',
        'telefono',
        'fecha_ingreso',
        'activo',
        'id_role',
        'user_id',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'activo' => 'boolean',
    ];


    // Relaciones
    public function proyectosResponsable(): HasMany
    {
        return $this->hasMany(Proyecto::class, 'responsable_proyecto', 'cod_empleado');
    }

    public function proyectosSupervisor(): HasMany
    {
        return $this->hasMany(Proyecto::class, 'supervisor_obra', 'cod_empleado');
    }

    public function proyectos(): BelongsToMany
    {
        return $this->belongsToMany(Proyecto::class, 'asignaciones_proyecto', 'cod_empleado', 'cod_proy')
                    ->withPivot(['rol', 'fecha_asignacion', 'fecha_fin', 'activo', 'observaciones'])
                    ->withTimestamps();
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionProyecto::class, 'cod_empleado', 'cod_empleado');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
