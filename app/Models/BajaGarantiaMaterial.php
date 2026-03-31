<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BajaGarantiaMaterial extends Model
{
    protected $table = 'bajas_garantia_material';
    protected $primaryKey = 'id_baja';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_reclamo',
        'id_stock',
        'id_material',
        'id_almacen',
        'id_lote',
        'cod_proy',
        'cantidad_baja',
        'motivo',
        'observaciones',
        'registrado_por',
        'fecha_baja',
    ];

    protected $casts = [
        'cantidad_baja' => 'decimal:2',
        'fecha_baja' => 'datetime',
    ];

    public function reclamo(): BelongsTo
    {
        return $this->belongsTo(ReclamoGarantiaMaterial::class, 'id_reclamo', 'id_reclamo');
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(StockAlmacen::class, 'id_stock', 'id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'id_material', 'id_material');
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'id_almacen', 'id_almacen');
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'cod_proy', 'cod_proy');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'registrado_por', 'cod_empleado');
    }
}

