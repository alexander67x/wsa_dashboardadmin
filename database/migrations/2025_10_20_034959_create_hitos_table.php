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
        Schema::create('hitos', function (Blueprint $table) {
            $table->integer('id_hito', true);
            $table->string('cod_proy')->index();
            $table->integer('id_fase')->nullable()->index();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->date('fecha_final_hito');
            $table->date('fecha_hito')->index();
            $table->enum('tipo', ['intermedio', 'entrega', 'revision'])->nullable()->default('intermedio');
            $table->boolean('es_critico')->nullable()->default(false);
            $table->enum('estado', ['pendiente', 'completado', 'atrasado'])->nullable()->default('pendiente')->index();
            $table->integer('creado_por')->index();
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->softDeletes();

            $table->index(['cod_proy', 'estado', 'fecha_hito'], 'idx_hitos_cod_proy_estado_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hitos');
    }
};
