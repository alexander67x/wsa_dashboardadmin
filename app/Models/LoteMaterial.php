<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoteMaterial extends Model
{
    protected $table = 'lote_material';
    protected $primaryKey = 'id_lote';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_material',
        'numero_lote',
        'numero_serie',
        'especificaciones',
        'fecha_ingreso',
        'fecha_vencimiento',
        'garantia_dias',
        'cod_proveedor',
        'estado_lote',
    ];

    protected $casts = [
        'fecha_ingreso' => 'datetime',
        'fecha_vencimiento' => 'date',
    ];

    // Relaciones
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'id_material', 'id_material');
    }

    public function stockAlmacen(): HasMany
    {
        return $this->hasMany(StockAlmacen::class, 'id_lote', 'id_lote');
    }

    public function solicitudItems(): HasMany
    {
        return $this->hasMany(SolicitudItem::class, 'id_lote', 'id_lote');
    }

    // Scopes
    public function scopeDisponibles($query)
    {
        return $query->where('estado_lote', 'disponible');
    }

    public function scopeVencidos($query)
    {
        return $query->where('estado_lote', 'vencido')
            ->orWhere(function ($q) {
                $q->whereNotNull('fecha_vencimiento')
                  ->where('fecha_vencimiento', '<', now());
            });
    }

    // Accessors
    public function getEstaVencidoAttribute(): bool
    {
        if (!$this->fecha_vencimiento) {
            return false;
        }

        return $this->fecha_vencimiento < now()->toDateString();
    }
}

