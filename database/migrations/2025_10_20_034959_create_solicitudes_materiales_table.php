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
        Schema::create('solicitudes_materiales', function (Blueprint $table) {
            $table->integer('id_solicitud', true);
            $table->string('numero_solicitud')->unique('numero_solicitud');
            $table->string('cod_proy')->index();
            $table->integer('id_tarea')->nullable()->index();
            $table->integer('solicitado_por')->index();
            $table->string('cargo_solicitante', 100)->nullable();
            $table->string('centro_costos', 50)->nullable();
            $table->timestamp('fecha_solicitud')->nullable()->useCurrent()->index();
            $table->date('fecha_requerida');
            $table->enum('estado', ['borrador', 'pendiente', 'aprobada', 'enviado', 'recibida', 'rechazada', 'cancelada'])->nullable()->default('borrador')->index();
            $table->boolean('requiere_aprobacion')->nullable()->default(true);
            $table->integer('aprobada_por')->nullable()->index('fk_solicitudes_materiales_aprobada_por');
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->string('motivo')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('urgente')->nullable()->default(false);
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
            $table->timestamp('updated_at')->nullable()->useCurrent();

            $table->index(['cod_proy', 'estado', 'fecha_solicitud'], 'idx_solicitudes_materiales_cod_proy_estado_fecha');
            $table->index(['numero_solicitud']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_materiales');
    }
};
