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

    protected $with = [
        'role.permissions',
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

    public function proyectosBasicos(): BelongsToMany
    {
        return $this->belongsToMany(Proyecto::class, 'asignaciones_proyecto', 'cod_empleado', 'cod_proy');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionProyecto::class, 'cod_empleado', 'cod_empleado');
    }

    public function tareas(): BelongsToMany
    {
        return $this->belongsToMany(Tarea::class, 'tarea_responsables', 'responsable_id', 'tarea_id')
            ->withTimestamps();
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function permissionCodes(): array
    {
        if (! $this->relationLoaded('role') && $this->role) {
            $this->loadMissing('role.permissions');
        }

        return $this->role?->permissions
            ->pluck('codigo')
            ->filter()
            ->unique()
            ->values()
            ->toArray() ?? [];
    }

    public function hasPermission(string $permission): bool
    {
        if (
            $this->role?->slug === 'supervisor'
            && in_array($permission, ['inventory.view.central', 'inventory.view.project', 'inventory.view.subwarehouses'], true)
        ) {
            return false;
        }

        if ($permission === 'materials.requests.approve' && $this->role?->slug === 'responsable_proyecto') {
            return false;
        }

        // Gerente General (rol 'gerencia') no debe poder registrar/crear movimientos de stock,
        // solo consultar inventario existente.
        if ($this->role?->slug === 'gerencia' && in_array($permission, [
            'inventory.movements.entries',
            'inventory.movements.exits',
            'inventory.movements.transfers',
        ], true)) {
            return false;
        }

        // Responsables de proyecto y supervisores deben poder:
        // - Registrar avances desde la app (reportes)
        // - Registrar incidencias desde la app
        if (
            in_array($this->role?->slug, ['responsable_proyecto', 'supervisor'], true)
            && in_array($permission, [
                'reports.create',
                'mobile.tasks.execute',
                'incidents.create',
                'mobile.incidents.report',
            ], true)
        ) {
            return true;
        }

        // Supervisores deben poder ver el detalle de solicitudes de materiales
        if ($this->role?->slug === 'supervisor' && $permission === 'materials.requests.view') {
            return true;
        }

        return in_array($permission, $this->permissionCodes(), true);
    }
}
