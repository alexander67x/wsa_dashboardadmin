<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanColumn extends Model
{
    protected $table = 'kanban_columns';
    protected $primaryKey = 'id_column';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'board_id',
        'nombre',
        'orden',
        'wip_limit',
        'es_entrada',
        'es_salida',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(KanbanBoard::class, 'board_id', 'id_board');
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'wip_column_id', 'id_column');
    }
}
