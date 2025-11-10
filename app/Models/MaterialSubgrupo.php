<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialSubgrupo extends Model
{
    protected $table = 'material_subgrupos';
    protected $primaryKey = 'id_subgrupo';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_grupo',
        'codigo_subgrupo',
        'nombre',
        'descripcion',
    ];

    // Relaciones
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(MaterialGrupo::class, 'id_grupo', 'id_grupo');
    }

    public function materiales(): HasMany
    {
        return $this->hasMany(Material::class, 'id_subgrupo', 'id_subgrupo');
    }
}
