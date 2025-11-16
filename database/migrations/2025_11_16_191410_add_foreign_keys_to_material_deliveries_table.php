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
        Schema::table('material_deliveries', function (Blueprint $table) {
            $table->foreign(['id_solicitud'], 'fk_material_deliveries_id_solicitud')
                ->references(['id_solicitud'])
                ->on('solicitudes_materiales')
                ->onUpdate('no action')
                ->onDelete('restrict');

            $table->foreign(['id_item'], 'fk_material_deliveries_id_item')
                ->references(['id_item'])
                ->on('solicitud_items')
                ->onUpdate('no action')
                ->onDelete('restrict');

            $table->foreign(['id_material'], 'fk_material_deliveries_id_material')
                ->references(['id_material'])
                ->on('materiales')
                ->onUpdate('no action')
                ->onDelete('restrict');

            $table->foreign(['id_lote'], 'fk_material_deliveries_id_lote')
                ->references(['id_lote'])
                ->on('lote_material')
                ->onUpdate('no action')
                ->onDelete('set null');

            $table->foreign(['id_almacen_origen'], 'fk_material_deliveries_id_almacen_origen')
                ->references(['id_almacen'])
                ->on('almacenes')
                ->onUpdate('no action')
                ->onDelete('restrict');

            $table->foreign(['id_almacen_destino'], 'fk_material_deliveries_id_almacen_destino')
                ->references(['id_almacen'])
                ->on('almacenes')
                ->onUpdate('no action')
                ->onDelete('restrict');

            $table->foreign(['entregado_por'], 'fk_material_deliveries_entregado_por')
                ->references(['cod_empleado'])
                ->on('empleados')
                ->onUpdate('no action')
                ->onDelete('restrict');

            $table->foreign(['recibido_por'], 'fk_material_deliveries_recibido_por')
                ->references(['cod_empleado'])
                ->on('empleados')
                ->onUpdate('no action')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_deliveries', function (Blueprint $table) {
            $table->dropForeign('fk_material_deliveries_id_solicitud');
            $table->dropForeign('fk_material_deliveries_id_item');
            $table->dropForeign('fk_material_deliveries_id_material');
            $table->dropForeign('fk_material_deliveries_id_lote');
            $table->dropForeign('fk_material_deliveries_id_almacen_origen');
            $table->dropForeign('fk_material_deliveries_id_almacen_destino');
            $table->dropForeign('fk_material_deliveries_entregado_por');
            $table->dropForeign('fk_material_deliveries_recibido_por');
        });
    }
};
