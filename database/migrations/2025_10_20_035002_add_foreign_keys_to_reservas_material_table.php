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
        Schema::table('reservas_material', function (Blueprint $table) {
            $table->foreign(['id_almacen'], 'fk_reservas_material_id_almacen')->references(['id_almacen'])->on('almacenes')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_lote'], 'fk_reservas_material_id_lote')->references(['id_lote'])->on('lote_material')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['id_material'], 'fk_reservas_material_id_material')->references(['id_material'])->on('materiales')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_solicitud'], 'fk_reservas_material_id_solicitud')->references(['id_solicitud'])->on('solicitudes_materiales')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['reservado_por'], 'fk_reservas_material_reservado_por')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas_material', function (Blueprint $table) {
            $table->dropForeign('fk_reservas_material_id_almacen');
            $table->dropForeign('fk_reservas_material_id_lote');
            $table->dropForeign('fk_reservas_material_id_material');
            $table->dropForeign('fk_reservas_material_id_solicitud');
            $table->dropForeign('fk_reservas_material_reservado_por');
        });
    }
};
