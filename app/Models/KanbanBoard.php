<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanBoard extends Model
{
    protected $table = 'kanban_boards';
    protected $primaryKey = 'id_board';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'cod_proy',
        'nombre',
        'activo',
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'cod_proy', 'cod_proy');
    }

    public function columns(): HasMany
    {
        return $this->hasMany(KanbanColumn::class, 'board_id', 'id_board')->orderBy('orden');
    }
}
