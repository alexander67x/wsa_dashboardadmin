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
        Schema::create('incidencias', function (Blueprint $table) {
            $table->integer('id_incidencia', true);
            $table->string('cod_proy');
            $table->integer('id_tarea')->nullable()->index();
            $table->string('titulo');
            $table->text('descripcion');
            $table->enum('tipo_incidencia', ['falla_equipos', 'accidente', 'retraso_material', 'problema_calidad', 'otro'])->index();
            $table->enum('severidad', ['critica', 'alta', 'media', 'baja'])->default('media')->index();
            $table->enum('estado', ['abierta', 'en_proceso', 'resuelta', 'verificacion', 'cerrada', 'reabierta'])->nullable()->default('abierta');
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->integer('reportado_por')->index();
            $table->integer('asignado_a')->nullable()->index();
            $table->timestamp('fecha_reportado')->nullable()->useCurrent()->index();
            $table->timestamp('fecha_resolucion')->nullable();
            $table->text('solucion_implementada')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
            $table->timestamp('updated_at')->nullable()->useCurrent();

            $table->index(['cod_proy', 'severidad'], 'idx_incidencias_cod_proy_severidad');
            $table->index(['cod_proy', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidencias');
    }
};
