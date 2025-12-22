<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReunionCliente extends Model
{
    protected $table = 'reunion_clientes';

    protected $fillable = [
        'cliente_id',
        'fecha_reunion',
        'tipo',
        'tema',
        'descripcion',
        'acuerdos',
        'proximo_seguimiento',
        'responsable_interno',
        'medio',
    ];

    protected $casts = [
        'fecha_reunion' => 'datetime',
        'proximo_seguimiento' => 'datetime',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'cod_cliente');
    }
}
