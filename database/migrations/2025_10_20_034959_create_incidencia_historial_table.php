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
        Schema::create('incidencia_historial', function (Blueprint $table) {
            $table->integer('id_hist', true);
            $table->integer('id_incidencia')->index();
            $table->string('estado_anterior')->nullable();
            $table->string('estado_nuevo');
            $table->text('comentario')->nullable();
            $table->string('accion_tomada')->nullable();
            $table->integer('usuario_cambio')->index();
            $table->timestamp('fecha_cambio')->nullable()->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidencia_historial');
    }
};
