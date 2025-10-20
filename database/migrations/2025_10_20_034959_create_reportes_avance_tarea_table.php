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
        Schema::create('reportes_avance_tarea', function (Blueprint $table) {
            $table->integer('id_reporte', true);
            $table->integer('id_tarea')->index();
            $table->string('cod_proy')->index();
            $table->string('titulo');
            $table->text('descripcion');
            $table->date('fecha_reporte')->index();
            $table->text('dificultades_encontradas')->nullable();
            $table->text('materiales_utilizados')->nullable();
            $table->integer('registrado_por')->index();
            $table->enum('estado', ['borrador', 'enviado', 'aprobado', 'rechazado'])->nullable()->default('enviado')->index();
            $table->text('observaciones_supervisor')->nullable();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->integer('aprobado_por')->nullable()->index('fk_reportes_avance_tarea_aprobado_por');
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes_avance_tarea');
    }
};
