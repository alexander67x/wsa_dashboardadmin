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
        Schema::create('exportaciones', function (Blueprint $table) {
            $table->integer('id_export', true);
            $table->string('cod_proy')->nullable()->index();
            $table->enum('tipo_export', ['pdf', 'excel', 'gantt', 'dashboard', 'otro'])->index();
            $table->text('filtros_aplicados')->nullable()->comment('JSON con filtros');
            $table->integer('generado_por')->index();
            $table->timestamp('fecha_generacion')->nullable()->useCurrent()->index();
            $table->string('ruta_generada')->nullable();
            $table->string('nombre_archivo')->nullable();
            $table->integer('tamano_bytes')->nullable();
            $table->enum('estado', ['generando', 'completado', 'error'])->nullable()->default('generando')->index();
            $table->text('metadata_json')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exportaciones');
    }
};
