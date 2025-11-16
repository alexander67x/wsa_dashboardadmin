<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolicitudMaterial extends Model
{
    protected $table = 'solicitudes_materiales';
    protected $primaryKey = 'id_solicitud';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'numero_solicitud',
        'cod_proy',
        'id_tarea',
        'solicitado_por',
        'cargo_solicitante',
        'centro_costos',
        'fecha_solicitud',
        'fecha_requerida',
        'estado',
        'requiere_aprobacion',
        'aprobada_por',
        'fecha_aprobacion',
        'motivo',
        'observaciones',
        'urgente',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_requerida' => 'date',
        'fecha_aprobacion' => 'datetime',
        'requiere_aprobacion' => 'boolean',
        'urgente' => 'boolean',
    ];

    // Relaciones
    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'cod_proy', 'cod_proy');
    }

    public function tarea(): BelongsTo
    {
        return $this->belongsTo(Tarea::class, 'id_tarea', 'id_tarea');
    }

    public function solicitadoPor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'solicitado_por', 'cod_empleado');
    }

    public function aprobadaPor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'aprobada_por', 'cod_empleado');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SolicitudItem::class, 'id_solicitud', 'id_solicitud');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(MaterialDelivery::class, 'id_solicitud', 'id_solicitud');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(SolicitudHistorial::class, 'id_solicitud', 'id_solicitud')->orderBy('fecha_evento', 'desc');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeAprobadas($query)
    {
        return $query->where('estado', 'aprobada');
    }

    public function scopeUrgentes($query)
    {
        return $query->where('urgente', true);
    }

    public function scopeRequierenCompra($query)
    {
        return $query->whereHas('items', function ($q) {
            $q->where('requiere_compra', true);
        });
    }

    // Accessors
    public function getPuedeAprobarAttribute(): bool
    {
        return in_array($this->estado, ['borrador', 'pendiente']);
    }

    public function getPuedeRechazarAttribute(): bool
    {
        return in_array($this->estado, ['borrador', 'pendiente']);
    }

    public function getPorcentajeEntregadoAttribute(): float
    {
        if (!$this->items || $this->items->isEmpty()) {
            return 0;
        }

        $totalSolicitado = $this->items->sum('cantidad_solicitada');
        $totalEntregado = $this->items->sum('cantidad_entregada');

        if ($totalSolicitado == 0) {
            return 0;
        }

        return ($totalEntregado / $totalSolicitado) * 100;
    }

    public function getRequiereCompraAttribute(): bool
    {
        if (!$this->items || $this->items->isEmpty()) {
            return false;
        }

        return $this->items->contains(function ($item) {
            return $item->requiere_compra === true;
        });
    }

    public function getItemsRequierenCompraAttribute(): int
    {
        if (!$this->items || $this->items->isEmpty()) {
            return 0;
        }

        return $this->items->filter(function ($item) {
            return $item->requiere_compra === true;
        })->count();
    }
}

