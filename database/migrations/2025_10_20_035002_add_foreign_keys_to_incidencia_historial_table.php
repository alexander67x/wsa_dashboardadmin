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
        Schema::table('incidencia_historial', function (Blueprint $table) {
            $table->foreign(['id_incidencia'], 'fk_incidencia_historial_id_incidencia')->references(['id_incidencia'])->on('incidencias')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['usuario_cambio'], 'fk_incidencia_historial_usuario_cambio')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidencia_historial', function (Blueprint $table) {
            $table->dropForeign('fk_incidencia_historial_id_incidencia');
            $table->dropForeign('fk_incidencia_historial_usuario_cambio');
        });
    }
};
