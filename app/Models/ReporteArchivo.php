<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteArchivo extends Model
{
    protected $table = 'reporte_archivos';
    public $timestamps = false;

    protected $fillable = [
        'id_reporte',
        'archivo_id',
        'es_foto_principal',
    ];

    protected $casts = [
        'es_foto_principal' => 'boolean',
    ];

    public function reporte(): BelongsTo
    {
        return $this->belongsTo(ReporteAvanceTarea::class, 'id_reporte', 'id_reporte');
    }

    public function archivo(): BelongsTo
    {
        return $this->belongsTo(Archivo::class, 'archivo_id', 'id_archivo');
    }
}






