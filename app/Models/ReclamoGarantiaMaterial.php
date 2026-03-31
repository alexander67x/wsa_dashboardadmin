<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReclamoGarantiaMaterial extends Model
{
    use SoftDeletes;

    protected $table = 'reclamos_garantia_material';

    protected $primaryKey = 'id_reclamo';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'id_stock',
        'id_material',
        'id_almacen',
        'id_lote',
        'cod_proy',
        'cantidad_reclamada',
        'estado',
        'resultado_final',
        'motivo_dano',
        'observaciones',
        'creado_por',
        'aprobado_por',
        'finalizado_por',
        'fecha_aprobacion',
        'fecha_finalizacion',
    ];

    protected $casts = [
        'cantidad_reclamada' => 'decimal:2',
        'fecha_aprobacion' => 'datetime',
        'fecha_finalizacion' => 'datetime',
    ];

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

    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteMaterial::class, 'id_lote', 'id_lote');
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'cod_proy', 'cod_proy');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'creado_por', 'cod_empleado');
    }

    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'aprobado_por', 'cod_empleado');
    }

    public function finalizadoPor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'finalizado_por', 'cod_empleado');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(ReclamoGarantiaHistorial::class, 'id_reclamo', 'id_reclamo')
            ->orderByDesc('created_at');
    }

    public function bajaGarantia(): HasOne
    {
        return $this->hasOne(BajaGarantiaMaterial::class, 'id_reclamo', 'id_reclamo');
    }
}
