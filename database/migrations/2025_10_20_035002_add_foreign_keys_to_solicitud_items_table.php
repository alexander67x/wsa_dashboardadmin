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
        Schema::table('solicitud_items', function (Blueprint $table) {
            $table->foreign(['id_lote'], 'fk_solicitud_items_id_lote')->references(['id_lote'])->on('lote_material')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['id_material'], 'fk_solicitud_items_id_material')->references(['id_material'])->on('materiales')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_solicitud'], 'fk_solicitud_items_id_solicitud')->references(['id_solicitud'])->on('solicitudes_materiales')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitud_items', function (Blueprint $table) {
            $table->dropForeign('fk_solicitud_items_id_lote');
            $table->dropForeign('fk_solicitud_items_id_material');
            $table->dropForeign('fk_solicitud_items_id_solicitud');
        });
    }
};
