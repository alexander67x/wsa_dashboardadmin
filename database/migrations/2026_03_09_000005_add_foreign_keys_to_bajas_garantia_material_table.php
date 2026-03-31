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
        Schema::table('bajas_garantia_material', function (Blueprint $table) {
            $table->foreign(['id_reclamo'], 'fk_bajas_garantia_id_reclamo')
                ->references(['id_reclamo'])
                ->on('reclamos_garantia_material')
                ->onUpdate('no action')
                ->onDelete('restrict');

            $table->foreign(['id_stock'], 'fk_bajas_garantia_id_stock')
                ->references(['id'])
                ->on('stock_almacen')
                ->onUpdate('no action')
                ->onDelete('restrict');

            $table->foreign(['id_material'], 'fk_bajas_garantia_id_material')
                ->references(['id_material'])
                ->on('materiales')
                ->onUpdate('no action')
                ->onDelete('restrict');

            $table->foreign(['id_almacen'], 'fk_bajas_garantia_id_almacen')
                ->references(['id_almacen'])
                ->on('almacenes')
                ->onUpdate('no action')
                ->onDelete('restrict');

            $table->foreign(['id_lote'], 'fk_bajas_garantia_id_lote')
                ->references(['id_lote'])
                ->on('lote_material')
                ->onUpdate('no action')
                ->onDelete('restrict');

            $table->foreign(['cod_proy'], 'fk_bajas_garantia_cod_proy')
                ->references(['cod_proy'])
                ->on('proyectos')
                ->onUpdate('no action')
                ->onDelete('restrict');

            $table->foreign(['registrado_por'], 'fk_bajas_garantia_registrado_por')
                ->references(['cod_empleado'])
                ->on('empleados')
                ->onUpdate('no action')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bajas_garantia_material', function (Blueprint $table) {
            $table->dropForeign('fk_bajas_garantia_id_reclamo');
            $table->dropForeign('fk_bajas_garantia_id_stock');
            $table->dropForeign('fk_bajas_garantia_id_material');
            $table->dropForeign('fk_bajas_garantia_id_almacen');
            $table->dropForeign('fk_bajas_garantia_id_lote');
            $table->dropForeign('fk_bajas_garantia_cod_proy');
            $table->dropForeign('fk_bajas_garantia_registrado_por');
        });
    }
};

