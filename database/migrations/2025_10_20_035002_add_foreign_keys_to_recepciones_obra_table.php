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
        Schema::table('recepciones_obra', function (Blueprint $table) {
            $table->foreign(['confirmado_por'], 'fk_recepciones_obra_confirmado_por')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['evidencia_archivo_id'], 'fk_recepciones_obra_evidencia_archivo_id')->references(['id_archivo'])->on('archivos')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['id_envio'], 'fk_recepciones_obra_id_envio')->references(['id_envio'])->on('envios')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recepciones_obra', function (Blueprint $table) {
            $table->dropForeign('fk_recepciones_obra_confirmado_por');
            $table->dropForeign('fk_recepciones_obra_evidencia_archivo_id');
            $table->dropForeign('fk_recepciones_obra_id_envio');
        });
    }
};
