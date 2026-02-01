<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE incidencias MODIFY COLUMN estado ENUM('abierta', 'en_proceso', 'resuelta', 'verificacion', 'registrada', 'cerrada', 'reabierta') NULL DEFAULT 'abierta'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Permitir temporalmente cualquier valor para normalizar datos existentes
        DB::statement("ALTER TABLE incidencias MODIFY COLUMN estado VARCHAR(50) NULL DEFAULT NULL");

        DB::table('incidencias')
            ->where(function ($query) {
                $query->whereNull('estado')
                    ->orWhere('estado', '')
                    ->orWhereRaw("TRIM(estado) = ''")
                    ->orWhereNotIn('estado', ['abierta', 'en_proceso', 'resuelta', 'verificacion', 'cerrada', 'reabierta']);
            })
            ->update(['estado' => 'abierta']);

        DB::statement("ALTER TABLE incidencias MODIFY COLUMN estado ENUM('abierta', 'en_proceso', 'resuelta', 'verificacion', 'cerrada', 'reabierta') NULL DEFAULT 'abierta'");
    }
};
