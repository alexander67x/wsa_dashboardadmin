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
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->foreign(['id_almacen_destino'], 'fk_movimientos_inventario_id_almacen_destino')->references(['id_almacen'])->on('almacenes')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['id_almacen_origen'], 'fk_movimientos_inventario_id_almacen_origen')->references(['id_almacen'])->on('almacenes')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['id_lote'], 'fk_movimientos_inventario_id_lote')->references(['id_lote'])->on('lote_material')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['id_material'], 'fk_movimientos_inventario_id_material')->references(['id_material'])->on('materiales')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['registrado_por'], 'fk_movimientos_inventario_registrado_por')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropForeign('fk_movimientos_inventario_id_almacen_destino');
            $table->dropForeign('fk_movimientos_inventario_id_almacen_origen');
            $table->dropForeign('fk_movimientos_inventario_id_lote');
            $table->dropForeign('fk_movimientos_inventario_id_material');
            $table->dropForeign('fk_movimientos_inventario_registrado_por');
        });
    }
};
