<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidenciaHistorial extends Model
{
    protected $table = 'incidencia_historial';
    protected $primaryKey = 'id_hist';
    
    public $timestamps = false;

    protected $fillable = [
        'id_incidencia',
        'estado_anterior',
        'estado_nuevo',
        'comentario',
        'accion_tomada',
        'usuario_cambio',
        'fecha_cambio',
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
    ];

    public function incidencia(): BelongsTo
    {
        return $this->belongsTo(Incidencia::class, 'id_incidencia', 'id_incidencia');
    }

    public function usuarioCambio(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'usuario_cambio', 'cod_empleado');
    }
}

