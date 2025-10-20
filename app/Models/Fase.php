<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fase extends Model
{
    use SoftDeletes;

    protected $table = 'fases';
    protected $primaryKey = 'id_fase';

    protected $fillable = [
        'cod_proy',
        'nombre_fase',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'orden',
        'estado',
        'porcentaje_avance',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'porcentaje_avance' => 'decimal:2',
    ];

    // Relaciones
    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'cod_proy', 'cod_proy');
    }
}
