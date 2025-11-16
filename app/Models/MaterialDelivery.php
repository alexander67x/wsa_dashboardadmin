<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialDelivery extends Model
{
    protected $table = 'material_deliveries';
    protected $primaryKey = 'id_entrega';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'numero_entrega',
        'id_solicitud',
        'id_item',
        'id_material',
        'id_lote',
        'id_almacen_origen',
        'id_almacen_destino',
        'cantidad_entregada',
        'cantidad_aprobada',
        'tipo_entrega',
        'motivo_parcial',
        'fecha_entrega',
        'entregado_por',
        'recibido_por',
        'observaciones',
        'estado',
        'fecha_recepcion',
    ];

    protected $casts = [
        'cantidad_entregada' => 'decimal:2',
        'cantidad_aprobada' => 'decimal:2',
        'fecha_entrega' => 'datetime',
        'fecha_recepcion' => 'datetime',
    ];

    // Relaciones
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudMaterial::class, 'id_solicitud', 'id_solicitud');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(SolicitudItem::class, 'id_item', 'id_item');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'id_material', 'id_material');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteMaterial::class, 'id_lote', 'id_lote');
    }

    public function almacenOrigen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'id_almacen_origen', 'id_almacen');
    }

    public function almacenDestino(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'id_almacen_destino', 'id_almacen');
    }

    public function entregadoPor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'entregado_por', 'cod_empleado');
    }

    public function recibidoPor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'recibido_por', 'cod_empleado');
    }
}
