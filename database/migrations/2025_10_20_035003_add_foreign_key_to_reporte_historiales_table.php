<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reporte_historiales', function (Blueprint $table) {
            $table->foreign('id_reporte')
                ->references('id_reporte')
                ->on('reportes_avance_tarea')
                ->cascadeOnDelete();

            $table->foreign('creado_por')
                ->references('cod_empleado')
                ->on('empleados')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reporte_historiales', function (Blueprint $table) {
            $table->dropForeign(['id_reporte']);
            $table->dropForeign(['creado_por']);
        });
    }
};
