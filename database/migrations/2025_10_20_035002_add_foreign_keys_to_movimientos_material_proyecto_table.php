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
        Schema::table('movimientos_material_proyecto', function (Blueprint $table) {
            $table->foreign(['cod_proy'], 'fk_movimientos_material_proyecto_cod_proy')->references(['cod_proy'])->on('proyectos')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_lote'], 'fk_movimientos_material_proyecto_id_lote')->references(['id_lote'])->on('lote_material')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['id_material'], 'fk_movimientos_material_proyecto_id_material')->references(['id_material'])->on('materiales')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_tarea'], 'fk_movimientos_material_proyecto_id_tarea')->references(['id_tarea'])->on('tareas')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['responsable'], 'fk_movimientos_material_proyecto_responsable')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos_material_proyecto', function (Blueprint $table) {
            $table->dropForeign('fk_movimientos_material_proyecto_cod_proy');
            $table->dropForeign('fk_movimientos_material_proyecto_id_lote');
            $table->dropForeign('fk_movimientos_material_proyecto_id_material');
            $table->dropForeign('fk_movimientos_material_proyecto_id_tarea');
            $table->dropForeign('fk_movimientos_material_proyecto_responsable');
        });
    }
};
