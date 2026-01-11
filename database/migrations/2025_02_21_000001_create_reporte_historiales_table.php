<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporte_historiales', function (Blueprint $table) {
            $table->bigIncrements('id_historial');
            $table->integer('id_reporte')->index();
            $table->string('tipo', 50);
            $table->text('comentario')->nullable();
            $table->json('metadata')->nullable();
            $table->integer('creado_por')->nullable()->index();
            $table->timestamps();

            $table->foreign('id_reporte')
                ->references('id_reporte')
                ->on('reportes_avance_tarea')
                ->cascadeOnDelete();

            $table->foreign('creado_por')
                ->references('cod_empleado')
                ->on('empleados')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporte_historiales');
    }
};
