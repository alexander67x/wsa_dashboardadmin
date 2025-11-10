<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Almacen extends Model
{
    protected $table = 'almacenes';
    protected $primaryKey = 'id_almacen';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'codigo_almacen',
        'nombre',
        'direccion',
        'ciudad',
        'pais',
        'latitud',
        'longitud',
        'tipo_ubicacion',
        'responsable',
        'tipo',
        'id_almacen_padre',
        'cod_proy',
        'activo',
    ];

    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'activo' => 'boolean',
    ];

    // Relaciones
    public function almacenPadre(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'id_almacen_padre', 'id_almacen');
    }

    public function subalmacenes(): HasMany
    {
        return $this->hasMany(Almacen::class, 'id_almacen_padre', 'id_almacen');
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'cod_proy', 'cod_proy');
    }

    public function responsableEmpleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'responsable', 'cod_empleado');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeCentrales($query)
    {
        return $query->where('tipo', 'central');
    }

    public function scopeDeProyecto($query)
    {
        return $query->where('tipo', 'proyecto');
    }

    // Accessors
    public function getEsCentralAttribute(): bool
    {
        return $this->tipo === 'central';
    }

    public function getTieneSubalmacenesAttribute(): bool
    {
        return $this->subalmacenes()->count() > 0;
    }
}
