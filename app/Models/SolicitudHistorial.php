<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudHistorial extends Model
{
    protected $table = 'solicitud_historial';
    protected $primaryKey = 'id_historial';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_solicitud',
        'tipo_evento',
        'descripcion',
        'detalles',
        'usuario_id',
        'empleado_id',
        'fecha_evento',
        'observaciones',
    ];

    protected $casts = [
        'detalles' => 'array',
        'fecha_evento' => 'datetime',
    ];

    // Relaciones
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudMaterial::class, 'id_solicitud', 'id_solicitud');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id', 'cod_empleado');
    }
}
