<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $timestamps = false;

    protected $table = 'roles';
    protected $primaryKey = 'id_role';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nombre',
        'descripcion',
        'es_global',
        'puede_aprobar_solicitudes',
        'puede_generar_reportes',
    ];

    protected $casts = [
        'es_global' => 'boolean',
        'puede_aprobar_solicitudes' => 'boolean',
        'puede_generar_reportes' => 'boolean',
    ];
}
