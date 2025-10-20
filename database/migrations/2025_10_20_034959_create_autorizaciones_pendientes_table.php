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
        Schema::create('autorizaciones_pendientes', function (Blueprint $table) {
            $table->integer('id_autorizacion', true);
            $table->string('entidad');
            $table->integer('entidad_id');
            $table->integer('flujo_id')->index();
            $table->integer('aprobador_requerido')->index();
            $table->integer('nivel_aprobacion');
            $table->timestamp('fecha_solicitud')->nullable()->useCurrent()->index();
            $table->timestamp('fecha_limite')->nullable();
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada', 'expirada'])->nullable()->default('pendiente')->index();
            $table->text('comentarios')->nullable();
            $table->timestamp('fecha_decision')->nullable();

            $table->index(['entidad', 'entidad_id'], 'autorizaciones_pendientes_entidad_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('autorizaciones_pendientes');
    }
};
