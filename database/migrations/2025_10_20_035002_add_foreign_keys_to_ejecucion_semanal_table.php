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
        Schema::table('ejecucion_semanal', function (Blueprint $table) {
            $table->foreign(['aprobado_por'], 'fk_ejecucion_semanal_aprobado_por')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['id_plan'], 'fk_ejecucion_semanal_id_plan')->references(['id_plan'])->on('planificacion_semanal')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['reportado_por'], 'fk_ejecucion_semanal_reportado_por')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ejecucion_semanal', function (Blueprint $table) {
            $table->dropForeign('fk_ejecucion_semanal_aprobado_por');
            $table->dropForeign('fk_ejecucion_semanal_id_plan');
            $table->dropForeign('fk_ejecucion_semanal_reportado_por');
        });
    }
};
