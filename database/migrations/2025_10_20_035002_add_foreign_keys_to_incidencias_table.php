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
        Schema::table('incidencias', function (Blueprint $table) {
            $table->foreign(['asignado_a'], 'fk_incidencias_asignado_a')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['cod_proy'], 'fk_incidencias_cod_proy')->references(['cod_proy'])->on('proyectos')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_tarea'], 'fk_incidencias_id_tarea')->references(['id_tarea'])->on('tareas')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['reportado_por'], 'fk_incidencias_reportado_por')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropForeign('fk_incidencias_asignado_a');
            $table->dropForeign('fk_incidencias_cod_proy');
            $table->dropForeign('fk_incidencias_id_tarea');
            $table->dropForeign('fk_incidencias_reportado_por');
        });
    }
};
