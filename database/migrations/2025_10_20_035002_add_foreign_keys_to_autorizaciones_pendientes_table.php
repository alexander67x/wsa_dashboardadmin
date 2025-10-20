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
        Schema::table('autorizaciones_pendientes', function (Blueprint $table) {
            $table->foreign(['aprobador_requerido'], 'fk_autorizaciones_pendientes_aprobador_requerido')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['flujo_id'], 'fk_autorizaciones_pendientes_flujo_id')->references(['id_flujo'])->on('flujos_aprobacion')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('autorizaciones_pendientes', function (Blueprint $table) {
            $table->dropForeign('fk_autorizaciones_pendientes_aprobador_requerido');
            $table->dropForeign('fk_autorizaciones_pendientes_flujo_id');
        });
    }
};
