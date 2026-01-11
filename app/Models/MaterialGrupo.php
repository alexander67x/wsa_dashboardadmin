<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialGrupo extends Model
{
    protected $table = 'material_grupos';
    protected $primaryKey = 'id_grupo';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'codigo_grupo',
        'nombre',
        'descripcion',
    ];

    public function subgrupos(): HasMany
    {
        return $this->hasMany(MaterialSubgrupo::class, 'id_grupo', 'id_grupo');
    }
}
