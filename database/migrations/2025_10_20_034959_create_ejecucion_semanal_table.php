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
        Schema::create('ejecucion_semanal', function (Blueprint $table) {
            $table->integer('id_ejecucion', true);
            $table->integer('id_plan')->index();
            $table->text('actividades_ejecutadas');
            $table->decimal('porcentaje_cumplimiento', 5)->comment('0-100%');
            $table->decimal('avance_real_porcentaje', 5);
            $table->text('causas_atraso')->nullable();
            $table->enum('estado', ['en_tiempo', 'atrasado', 'adelantado'])->index();
            $table->integer('reportado_por')->index();
            $table->timestamp('fecha_reporte')->nullable()->useCurrent()->index();
            $table->integer('aprobado_por')->nullable()->index();
            $table->timestamp('fecha_aprobacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ejecucion_semanal');
    }
};
