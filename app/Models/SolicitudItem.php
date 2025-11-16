<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudItem extends Model
{
    protected $table = 'solicitud_items';
    protected $primaryKey = 'id_item';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_solicitud',
        'id_material',
        'id_lote',
        'cantidad_solicitada',
        'cantidad_disponible_padre',
        'cantidad_faltante',
        'requiere_compra',
        'cantidad_aprobada',
        'cantidad_entregada',
        'unidad',
        'justificacion',
        'observaciones',
    ];

    protected $casts = [
        'cantidad_solicitada' => 'decimal:2',
        'cantidad_disponible_padre' => 'decimal:2',
        'cantidad_faltante' => 'decimal:2',
        'requiere_compra' => 'boolean',
        'cantidad_aprobada' => 'decimal:2',
        'cantidad_entregada' => 'decimal:2',
    ];

    // Relaciones
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudMaterial::class, 'id_solicitud', 'id_solicitud');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'id_material', 'id_material');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteMaterial::class, 'id_lote', 'id_lote');
    }

    // Accessors
    public function getPorcentajeEntregadoAttribute(): float
    {
        if ($this->cantidad_aprobada == 0) {
            return 0;
        }

        return ($this->cantidad_entregada / $this->cantidad_aprobada) * 100;
    }

    public function getPendienteEntregarAttribute(): float
    {
        return max(0, ($this->cantidad_aprobada ?? $this->cantidad_solicitada) - $this->cantidad_entregada);
    }
}

