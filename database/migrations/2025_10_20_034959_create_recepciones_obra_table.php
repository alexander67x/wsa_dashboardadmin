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
        Schema::create('recepciones_obra', function (Blueprint $table) {
            $table->integer('id_recepcion', true);
            $table->integer('id_envio')->index();
            $table->integer('confirmado_por')->index();
            $table->timestamp('fecha_recepcion')->index();
            $table->enum('estado_recepcion', ['completa', 'parcial', 'con_observaciones'])->index();
            $table->text('observaciones')->nullable();
            $table->integer('evidencia_archivo_id')->nullable()->index();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recepciones_obra');
    }
};
