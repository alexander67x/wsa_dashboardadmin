<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ubicacion extends Model
{
    use SoftDeletes;

    protected $table = 'ubicaciones';
    protected $primaryKey = 'cod_ubicacion';

    protected $fillable = [
        'nombre_ubicacion',
        'direccion',
        'ciudad',
        'pais',
        'latitud',
        'longitud',
        'tipo_ubicacion',
        'activo',
    ];

    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'activo' => 'boolean',
    ];

    // Relaciones
    public function proyectos(): HasMany
    {
        return $this->hasMany(Proyecto::class, 'cod_ubicacion', 'cod_ubicacion');
    }
}
