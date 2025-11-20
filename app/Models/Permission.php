<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    public $timestamps = false;

    protected $table = 'permissions';

    protected $primaryKey = 'id_permission';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'codigo',
        'descripcion',
        'modulo',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id');
    }
}
