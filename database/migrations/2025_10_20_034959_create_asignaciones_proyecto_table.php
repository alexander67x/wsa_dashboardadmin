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
        Schema::create('asignaciones_proyecto', function (Blueprint $table) {
            $table->integer('id_asignacion', true);
            $table->string('cod_proy')->index();
            $table->integer('cod_empleado')->index();
            $table->date('fecha_inicio_asignacion')->index();
            $table->date('fecha_fin_asignacion')->nullable();
            $table->string('rol_en_proyecto');
            $table->enum('estado', ['activo', 'finalizado', 'suspendido'])->default('activo')->index();
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones_proyecto');
    }
};
