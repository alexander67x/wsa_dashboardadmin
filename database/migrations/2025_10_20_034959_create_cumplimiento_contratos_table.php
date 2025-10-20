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
        Schema::create('cumplimiento_contratos', function (Blueprint $table) {
            $table->integer('id_cumplimiento', true);
            $table->string('cod_proy')->index();
            $table->date('fecha_evaluacion')->index();
            $table->decimal('porcentaje_cumplimiento', 5);
            $table->decimal('porcentaje_avance_fisico', 5)->nullable();
            $table->decimal('porcentaje_avance_financiero', 5)->nullable();
            $table->text('observaciones')->nullable();
            $table->integer('evaluador')->index();
            $table->enum('tipo_evaluacion', ['mensual', 'trimestral', 'hito', 'final'])->index();
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cumplimiento_contratos');
    }
};
