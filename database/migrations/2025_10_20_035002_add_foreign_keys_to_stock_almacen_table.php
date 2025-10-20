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
        Schema::table('stock_almacen', function (Blueprint $table) {
            $table->foreign(['id_almacen'], 'fk_stock_almacen_id_almacen')->references(['id_almacen'])->on('almacenes')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_lote'], 'fk_stock_almacen_id_lote')->references(['id_lote'])->on('lote_material')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['id_material'], 'fk_stock_almacen_id_material')->references(['id_material'])->on('materiales')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_almacen', function (Blueprint $table) {
            $table->dropForeign('fk_stock_almacen_id_almacen');
            $table->dropForeign('fk_stock_almacen_id_lote');
            $table->dropForeign('fk_stock_almacen_id_material');
        });
    }
};
