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
        Schema::table('incidencia_evidencias', function (Blueprint $table) {
            $table->foreign(['archivo_id'], 'fk_incidencia_evidencias_archivo_id')->references(['id_archivo'])->on('archivos')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_incidencia'], 'fk_incidencia_evidencias_id_incidencia')->references(['id_incidencia'])->on('incidencias')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidencia_evidencias', function (Blueprint $table) {
            $table->dropForeign('fk_incidencia_evidencias_archivo_id');
            $table->dropForeign('fk_incidencia_evidencias_id_incidencia');
        });
    }
};
