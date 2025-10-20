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
        Schema::create('configuracion_alertas', function (Blueprint $table) {
            $table->integer('id_config', true);
            $table->integer('cod_empleado')->index();
            $table->string('tipo_alerta')->index();
            $table->boolean('activa')->nullable()->default(true)->index();
            $table->enum('frecuencia', ['inmediata', 'diaria', 'semanal'])->nullable()->default('inmediata');
            $table->text('configuracion_json')->nullable()->comment('parámetros específicos de la alerta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_alertas');
    }
};
