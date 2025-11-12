<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Archivo extends Model
{
    protected $table = 'archivos';
    protected $primaryKey = 'id_archivo';
    public $timestamps = false;

    protected $fillable = [
        'entidad',
        'entidad_id',
        'nombre_original',
        'ruta_storage',
        'tipo_mime',
        'tamano_bytes',
        'es_foto',
        'latitud',
        'longitud',
        'tomado_en',
        'es_evidencia_principal',
        'creado_por',
    ];

    protected $casts = [
        'latitud' => 'float',
        'longitud' => 'float',
        'tomado_en' => 'datetime',
        'es_foto' => 'boolean',
        'es_evidencia_principal' => 'boolean',
    ];

    protected $appends = ['url'];

    public function reportes(): BelongsToMany
    {
        return $this->belongsToMany(
            ReporteAvanceTarea::class,
            'reporte_archivos',
            'archivo_id',
            'id_reporte'
        )->withPivot('es_foto_principal');
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->ruta_storage) {
            return null;
        }

        try {
            return Storage::url($this->ruta_storage);
        } catch (\Throwable) {
            return $this->ruta_storage;
        }
    }
}






