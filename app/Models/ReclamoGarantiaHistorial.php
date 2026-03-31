<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReclamoGarantiaHistorial extends Model
{
    protected $table = 'reclamos_garantia_historial';

    protected $primaryKey = 'id_historial';

    public $incrementing = true;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id_reclamo',
        'estado_anterior',
        'estado_nuevo',
        'accion',
        'comentario',
        'creado_por',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function reclamo(): BelongsTo
    {
        return $this->belongsTo(ReclamoGarantiaMaterial::class, 'id_reclamo', 'id_reclamo');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'creado_por', 'cod_empleado');
    }
}
