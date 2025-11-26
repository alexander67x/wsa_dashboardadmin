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
        DB::statement("ALTER TABLE hitos MODIFY COLUMN estado ENUM('pendiente', 'completado', 'atrasado') NULL DEFAULT 'pendiente'");
    }
};
