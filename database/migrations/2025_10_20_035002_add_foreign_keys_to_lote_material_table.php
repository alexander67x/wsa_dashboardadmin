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
        Schema::table('lote_material', function (Blueprint $table) {
            $table->foreign(['cod_proveedor'], 'fk_lote_material_cod_proveedor')->references(['cod_proveedor'])->on('proveedores')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['id_material'], 'fk_lote_material_id_material')->references(['id_material'])->on('materiales')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lote_material', function (Blueprint $table) {
            $table->dropForeign('fk_lote_material_cod_proveedor');
            $table->dropForeign('fk_lote_material_id_material');
        });
    }
};
