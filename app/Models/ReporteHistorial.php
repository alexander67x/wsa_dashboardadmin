<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteHistorial extends Model
{
    protected $table = 'reporte_historiales';
    protected $primaryKey = 'id_historial';

    protected $fillable = [
        'id_reporte',
        'tipo',
        'comentario',
        'metadata',
        'creado_por',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function reporte(): BelongsTo
    {
        return $this->belongsTo(ReporteAvanceTarea::class, 'id_reporte', 'id_reporte');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'creado_por', 'cod_empleado');
    }
}
