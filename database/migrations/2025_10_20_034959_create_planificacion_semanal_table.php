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
        Schema::create('planificacion_semanal', function (Blueprint $table) {
            $table->integer('id_plan', true);
            $table->string('cod_proy')->index();
            $table->integer('semana')->comment('Número de semana del año');
            $table->integer('año');
            $table->text('actividades_planificadas');
            $table->text('hitos_esperados')->nullable();
            $table->text('recursos_requeridos')->nullable();
            $table->decimal('avance_esperado_porcentaje', 5)->nullable()->default(0);
            $table->enum('estado', ['planificado', 'en_ejecucion', 'completado', 'atrasado'])->nullable()->default('planificado')->index();
            $table->integer('planificado_por')->index();
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
            $table->timestamp('updated_at')->nullable()->useCurrent();

            $table->index(['cod_proy', 'semana', 'año'], 'idx_planificacion_semanal_cod_proy_semana');
            $table->index(['semana', 'año']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planificacion_semanal');
    }
};
