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
        Schema::table('solicitudes_materiales', function (Blueprint $table) {
            $table->foreign(['aprobada_por'], 'fk_solicitudes_materiales_aprobada_por')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['cod_proy'], 'fk_solicitudes_materiales_cod_proy')->references(['cod_proy'])->on('proyectos')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_tarea'], 'fk_solicitudes_materiales_id_tarea')->references(['id_tarea'])->on('tareas')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['solicitado_por'], 'fk_solicitudes_materiales_solicitado_por')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitudes_materiales', function (Blueprint $table) {
            $table->dropForeign('fk_solicitudes_materiales_aprobada_por');
            $table->dropForeign('fk_solicitudes_materiales_cod_proy');
            $table->dropForeign('fk_solicitudes_materiales_id_tarea');
            $table->dropForeign('fk_solicitudes_materiales_solicitado_por');
        });
    }
};
