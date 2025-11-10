<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $table = 'materiales';
    protected $primaryKey = 'id_material';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'codigo_producto',
        'nombre_producto',
        'id_subgrupo',
        'unidad_medida',
        'costo_unitario_promedio_bs',
        'equivalencia',
        'unidad_equivalencia',
        'stock_minimo',
        'stock_maximo',
        'criticidad',
        'activo',
    ];

    protected $casts = [
        'costo_unitario_promedio_bs' => 'decimal:2',
        'equivalencia' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'stock_maximo' => 'decimal:2',
        'activo' => 'boolean',
    ];

    // Relaciones
    public function subgrupo(): BelongsTo
    {
        return $this->belongsTo(MaterialSubgrupo::class, 'id_subgrupo', 'id_subgrupo');
    }

    public function almacenes(): BelongsToMany
    {
        return $this->belongsToMany(Almacen::class, 'stock_almacen', 'id_material', 'id_almacen')
            ->withPivot([
                'id',
                'id_lote',
                'cantidad_disponible',
                'cantidad_reservada',
                'cantidad_minima_alerta',
                'ubicacion_fisica',
                'updated_at',
            ]);
    }

    public function stockAlmacen(): HasMany
    {
        return $this->hasMany(StockAlmacen::class, 'id_material', 'id_material');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeCriticos($query)
    {
        return $query->where('criticidad', 'critico');
    }

    // Accessors
    public function getStockTotalAttribute(): float
    {
        return $this->stockAlmacen()->sum('cantidad_disponible');
    }

    public function getStockReservadoTotalAttribute(): float
    {
        return $this->stockAlmacen()->sum('cantidad_reservada');
    }

    public function getStockDisponibleTotalAttribute(): float
    {
        return $this->getStockTotalAttribute() - $this->getStockReservadoTotalAttribute();
    }
}
