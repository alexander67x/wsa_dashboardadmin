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
        Schema::table('tareas', function (Blueprint $table) {
            $table->foreign(['cod_proy'], 'fk_tareas_cod_proy')->references(['cod_proy'])->on('proyectos')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_fase'], 'fk_tareas_id_fase')->references(['id_fase'])->on('fases')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['parent_id'], 'fk_tareas_parent_id')->references(['id_tarea'])->on('tareas')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['responsable_id'], 'fk_tareas_responsable_id')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['supervisor_asignado'], 'fk_tareas_supervisor_asignado')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['wip_column_id'], 'fk_tareas_wip_column_id')->references(['id_column'])->on('kanban_columns')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->dropForeign('fk_tareas_cod_proy');
            $table->dropForeign('fk_tareas_id_fase');
            $table->dropForeign('fk_tareas_parent_id');
            $table->dropForeign('fk_tareas_responsable_id');
            $table->dropForeign('fk_tareas_supervisor_asignado');
            $table->dropForeign('fk_tareas_wip_column_id');
        });
    }
};
