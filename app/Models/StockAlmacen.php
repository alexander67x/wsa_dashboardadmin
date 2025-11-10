<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAlmacen extends Model
{
    protected $table = 'stock_almacen';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_almacen',
        'id_material',
        'id_lote',
        'cantidad_disponible',
        'cantidad_reservada',
        'cantidad_minima_alerta',
        'ubicacion_fisica',
    ];

    protected $casts = [
        'cantidad_disponible' => 'decimal:2',
        'cantidad_reservada' => 'decimal:2',
        'cantidad_minima_alerta' => 'decimal:2',
    ];

    // Relaciones
    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'id_almacen', 'id_almacen');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'id_material', 'id_material');
    }

    // Relación con lote - descomentar cuando exista el modelo LoteMaterial
    // public function lote(): BelongsTo
    // {
    //     return $this->belongsTo(LoteMaterial::class, 'id_lote', 'id_lote');
    // }

    // Accessors
    public function getCantidadDisponibleRealAttribute(): float
    {
        return $this->cantidad_disponible - $this->cantidad_reservada;
    }

    public function getNecesitaReposicionAttribute(): bool
    {
        return $this->cantidad_disponible <= $this->cantidad_minima_alerta;
    }
}
