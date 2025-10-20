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
        Schema::table('reportes_avance_tarea', function (Blueprint $table) {
            $table->foreign(['aprobado_por'], 'fk_reportes_avance_tarea_aprobado_por')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['cod_proy'], 'fk_reportes_avance_tarea_cod_proy')->references(['cod_proy'])->on('proyectos')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_tarea'], 'fk_reportes_avance_tarea_id_tarea')->references(['id_tarea'])->on('tareas')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['registrado_por'], 'fk_reportes_avance_tarea_registrado_por')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reportes_avance_tarea', function (Blueprint $table) {
            $table->dropForeign('fk_reportes_avance_tarea_aprobado_por');
            $table->dropForeign('fk_reportes_avance_tarea_cod_proy');
            $table->dropForeign('fk_reportes_avance_tarea_id_tarea');
            $table->dropForeign('fk_reportes_avance_tarea_registrado_por');
        });
    }
};
