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
        Schema::table('reclamos_garantia_material', function (Blueprint $table) {
            $table->foreign(['id_stock'], 'fk_reclamos_garantia_material_id_stock')
                ->references(['id'])->on('stock_almacen')
                ->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_material'], 'fk_reclamos_garantia_material_id_material')
                ->references(['id_material'])->on('materiales')
                ->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_almacen'], 'fk_reclamos_garantia_material_id_almacen')
                ->references(['id_almacen'])->on('almacenes')
                ->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_lote'], 'fk_reclamos_garantia_material_id_lote')
                ->references(['id_lote'])->on('lote_material')
                ->onUpdate('no action')->onDelete('set null');
            $table->foreign(['cod_proy'], 'fk_reclamos_garantia_material_cod_proy')
                ->references(['cod_proy'])->on('proyectos')
                ->onUpdate('no action')->onDelete('set null');
            $table->foreign(['creado_por'], 'fk_reclamos_garantia_material_creado_por')
                ->references(['cod_empleado'])->on('empleados')
                ->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['aprobado_por'], 'fk_reclamos_garantia_material_aprobado_por')
                ->references(['cod_empleado'])->on('empleados')
                ->onUpdate('no action')->onDelete('set null');
            $table->foreign(['finalizado_por'], 'fk_reclamos_garantia_material_finalizado_por')
                ->references(['cod_empleado'])->on('empleados')
                ->onUpdate('no action')->onDelete('set null');
        });

        Schema::table('reclamos_garantia_historial', function (Blueprint $table) {
            $table->foreign(['id_reclamo'], 'fk_reclamos_garantia_historial_id_reclamo')
                ->references(['id_reclamo'])->on('reclamos_garantia_material')
                ->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['creado_por'], 'fk_reclamos_garantia_historial_creado_por')
                ->references(['cod_empleado'])->on('empleados')
                ->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reclamos_garantia_historial', function (Blueprint $table) {
            $table->dropForeign('fk_reclamos_garantia_historial_id_reclamo');
            $table->dropForeign('fk_reclamos_garantia_historial_creado_por');
        });

        Schema::table('reclamos_garantia_material', function (Blueprint $table) {
            $table->dropForeign('fk_reclamos_garantia_material_id_stock');
            $table->dropForeign('fk_reclamos_garantia_material_id_material');
            $table->dropForeign('fk_reclamos_garantia_material_id_almacen');
            $table->dropForeign('fk_reclamos_garantia_material_id_lote');
            $table->dropForeign('fk_reclamos_garantia_material_cod_proy');
            $table->dropForeign('fk_reclamos_garantia_material_creado_por');
            $table->dropForeign('fk_reclamos_garantia_material_aprobado_por');
            $table->dropForeign('fk_reclamos_garantia_material_finalizado_por');
        });
    }
};
