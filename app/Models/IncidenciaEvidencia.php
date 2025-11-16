<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidenciaEvidencia extends Model
{
    protected $table = 'incidencia_evidencias';
    protected $primaryKey = 'id';
    
    public $timestamps = false;

    protected $fillable = [
        'id_incidencia',
        'archivo_id',
        'descripcion',
    ];

    public function incidencia(): BelongsTo
    {
        return $this->belongsTo(Incidencia::class, 'id_incidencia', 'id_incidencia');
    }

    public function archivo(): BelongsTo
    {
        return $this->belongsTo(Archivo::class, 'archivo_id', 'id_archivo');
    }
}

