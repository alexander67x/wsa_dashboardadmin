<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Archivo;

class Cliente extends Model
{
    use SoftDeletes;

    protected $table = 'clientes';
    protected $primaryKey = 'cod_cliente';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nombre_cliente',
        'industria',
        'contacto_principal',
        'email',
        'telefono',
        'direccion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relaciones
    public function proyectos(): HasMany
    {
        return $this->hasMany(Proyecto::class, 'cod_cliente', 'cod_cliente');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(Archivo::class, 'entidad_id', 'cod_cliente')
            ->where('entidad', 'clientes');
    }
}
