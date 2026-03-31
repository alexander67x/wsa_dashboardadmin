<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'garantia_dias',
        'ubicacion_fisica',
    ];

    protected $casts = [
        'cantidad_disponible' => 'decimal:2',
        'cantidad_reservada' => 'decimal:2',
        'cantidad_minima_alerta' => 'decimal:2',
        'garantia_dias' => 'integer',
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

    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteMaterial::class, 'id_lote', 'id_lote');
    }

    public function reclamosGarantia(): HasMany
    {
        return $this->hasMany(ReclamoGarantiaMaterial::class, 'id_stock', 'id');
    }

    // Accessors
    public function getCantidadDisponibleRealAttribute(): float
    {
        return $this->cantidad_disponible - $this->cantidad_reservada;
    }

    public function getNecesitaReposicionAttribute(): bool
    {
        return $this->cantidad_disponible <= $this->cantidad_minima_alerta;
    }

    public function getGarantiaRestanteDiasAttribute(): ?int
    {
        if ($this->garantia_dias === null || $this->created_at === null) {
            return null;
        }

        $transcurridos = $this->created_at->startOfDay()->diffInDays(now()->startOfDay());

        return max(0, $this->garantia_dias - $transcurridos);
    }

    public function getFechaFinGarantiaAttribute(): ?string
    {
        if ($this->garantia_dias === null || $this->created_at === null) {
            return null;
        }

        return $this->created_at
            ->copy()
            ->addDays((int) $this->garantia_dias)
            ->toDateString();
    }
}
