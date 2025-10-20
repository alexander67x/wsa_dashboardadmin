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
        Schema::create('objetivos', function (Blueprint $table) {
            $table->integer('id_objetivo', true);
            $table->string('cod_proy')->index();
            $table->text('descripcion');
            $table->string('indicador')->nullable();
            $table->integer('porcentaje_completado')->nullable();
            $table->date('fecha_objetivo')->nullable()->index();
            $table->enum('estado', ['pendiente', 'en_progreso', 'completado', 'no_alcanzado'])->nullable()->default('pendiente')->index();
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objetivos');
    }
};
