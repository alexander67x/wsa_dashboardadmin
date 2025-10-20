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
        Schema::create('metricas_desempeno', function (Blueprint $table) {
            $table->integer('id_metrica', true);
            $table->string('cod_proy')->index();
            $table->integer('cod_empleado')->index();
            $table->date('fecha_evaluacion')->index();
            $table->integer('tareas_completadas')->nullable()->default(0);
            $table->integer('tareas_en_tiempo')->nullable()->default(0);
            $table->integer('tareas_atrasadas')->nullable()->default(0);
            $table->decimal('calidad_trabajo', 3)->nullable()->comment('1-5 estrellas');
            $table->decimal('cumplimiento_reportes', 5)->nullable()->comment('% reportes entregados a tiempo');
            $table->text('observaciones')->nullable();
            $table->integer('evaluado_por')->index();
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metricas_desempeno');
    }
};
