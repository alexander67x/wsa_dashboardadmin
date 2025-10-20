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
        Schema::table('reporte_archivos', function (Blueprint $table) {
            $table->foreign(['archivo_id'], 'fk_reporte_archivos_archivo_id')->references(['id_archivo'])->on('archivos')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_reporte'], 'fk_reporte_archivos_id_reporte')->references(['id_reporte'])->on('reportes_avance_tarea')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reporte_archivos', function (Blueprint $table) {
            $table->dropForeign('fk_reporte_archivos_archivo_id');
            $table->dropForeign('fk_reporte_archivos_id_reporte');
        });
    }
};
