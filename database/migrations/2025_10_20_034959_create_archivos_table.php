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
        Schema::create('archivos', function (Blueprint $table) {
            $table->integer('id_archivo', true);
            $table->string('entidad')->nullable()->comment('tareas, reportes, incidencias, cotizaciones, etc.');
            $table->integer('entidad_id');
            $table->string('nombre_original');
            $table->string('ruta_storage');
            $table->string('tipo_mime')->nullable();
            $table->integer('tamano_bytes')->nullable();
            $table->boolean('es_foto')->nullable()->default(false)->index();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->timestamp('tomado_en')->nullable();
            $table->boolean('es_evidencia_principal')->nullable()->default(false);
            $table->integer('creado_por')->index();
            $table->timestamp('created_at')->nullable()->useCurrent()->index();

            $table->index(['entidad', 'entidad_id'], 'archivos_entidad_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archivos');
    }
};
