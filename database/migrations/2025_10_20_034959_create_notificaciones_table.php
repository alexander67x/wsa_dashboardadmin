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
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->integer('id_notificacion', true);
            $table->enum('tipo', ['atraso_planificacion', 'stock_bajo', 'aprobacion_pendiente', 'vencimiento_cotizacion', 'hito_proximo', 'incidencia_critica'])->index();
            $table->string('entidad');
            $table->integer('entidad_id');
            $table->integer('destinatario')->index();
            $table->text('mensaje');
            $table->boolean('leida')->nullable()->default(false)->index();
            $table->boolean('requiere_accion')->nullable()->default(false)->index();
            $table->timestamp('fecha_creacion')->nullable()->useCurrent()->index();
            $table->timestamp('fecha_lectura')->nullable();

            $table->index(['entidad', 'entidad_id'], 'notificaciones_entidad_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
