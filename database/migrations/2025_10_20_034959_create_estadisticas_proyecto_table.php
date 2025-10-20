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
        Schema::create('estadisticas_proyecto', function (Blueprint $table) {
            $table->integer('id_estadistica', true);
            $table->string('cod_proy')->index();
            $table->integer('total_empleados_asignados')->default(0);
            $table->decimal('porcentaje_avance_promedio', 5)->default(0);
            $table->integer('dias_retraso')->nullable()->default(0);
            $table->decimal('presupuesto_ejecutado', 14)->nullable()->default(0);
            $table->decimal('costo_materiales', 14)->nullable()->default(0);
            $table->decimal('costo_mano_obra', 14)->nullable()->default(0);
            $table->integer('total_incidencias')->nullable()->default(0);
            $table->integer('incidencias_criticas')->nullable()->default(0);
            $table->timestamp('fecha_ultima_actualizacion')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estadisticas_proyecto');
    }
};
