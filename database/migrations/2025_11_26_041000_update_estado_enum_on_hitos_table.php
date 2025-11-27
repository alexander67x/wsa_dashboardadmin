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
        DB::statement("ALTER TABLE hitos MODIFY COLUMN estado ENUM('pendiente', 'en_ejecucion', 'completado', 'atrasado') NULL DEFAULT 'pendiente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Permitir temporalmente cualquier valor para normalizar datos existentes
        DB::statement("ALTER TABLE hitos MODIFY COLUMN estado VARCHAR(50) NULL DEFAULT NULL");

        DB::table('hitos')
            ->where(function ($query) {
                $query->whereNull('estado')
                    ->orWhere('estado', '')
                    ->orWhereRaw("TRIM(estado) = ''")
                    ->orWhereNotIn('estado', ['pendiente', 'completado', 'atrasado']);
            })
            ->update(['estado' => 'pendiente']);

        DB::statement("ALTER TABLE hitos MODIFY COLUMN estado ENUM('pendiente', 'completado', 'atrasado') NULL DEFAULT 'pendiente'");
    }
};
