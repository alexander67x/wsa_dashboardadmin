<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('historial_estados_proyecto', function (Blueprint $table) {
            $table->foreign(['cod_proy'], 'fk_historial_estados_proyecto_cod_proy')->references(['cod_proy'])->on('proyectos')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['usuario_cambio'], 'fk_historial_estados_proyecto_usuario_cambio')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historial_estados_proyecto', function (Blueprint $table) {
            $table->dropForeign('fk_historial_estados_proyecto_cod_proy');
            $table->dropForeign('fk_historial_estados_proyecto_usuario_cambio');
        });
    }
};
