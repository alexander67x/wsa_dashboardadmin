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
        Schema::create('tareas', function (Blueprint $table) {
            $table->integer('id_tarea', true);
            $table->string('cod_proy');
            $table->integer('id_fase')->nullable()->index();
            $table->integer('parent_id')->nullable()->index()->comment('null si es tarea principal');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio')->nullable()->index();
            $table->date('fecha_fin')->nullable()->index();
            $table->integer('duracion_dias')->nullable()->comment('duración en días hábiles');
            $table->enum('prioridad', ['alta', 'media', 'baja'])->nullable()->default('media')->index();
            $table->enum('estado', ['pendiente', 'en_proceso', 'en_pausa', 'en_revision', 'finalizada', 'cancelada'])->nullable()->default('pendiente');
            $table->integer('responsable_id')->index();
            $table->integer('supervisor_asignado')->nullable()->index();
            $table->integer('wip_column_id')->nullable()->index();
            $table->boolean('checkpoint_required')->nullable()->default(false);
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->softDeletes();

            $table->index(['cod_proy', 'responsable_id'], 'idx_tareas_cod_proy_responsable');
            $table->index(['cod_proy', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
