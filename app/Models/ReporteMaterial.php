<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteMaterial extends Model
{
    protected $table = 'reporte_materiales';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_reporte',
        'id_material',
        'cantidad_usada',
        'unidad_medida',
        'observaciones',
    ];

    protected $casts = [
        'cantidad_usada' => 'decimal:2',
    ];

    public function reporte(): BelongsTo
    {
        return $this->belongsTo(ReporteAvanceTarea::class, 'id_reporte', 'id_reporte');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'id_material', 'id_material');
    }
}
