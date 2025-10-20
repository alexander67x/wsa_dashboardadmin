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
        Schema::create('flujos_aprobacion', function (Blueprint $table) {
            $table->integer('id_flujo', true);
            $table->string('entidad')->index()->comment('solicitudes_materiales, etc.');
            $table->string('tipo_operacion')->nullable()->comment('crear, modificar, eliminar');
            $table->integer('orden_aprobacion');
            $table->string('rol_aprobador')->nullable();
            $table->integer('empleado_aprobador')->nullable()->index();
            $table->boolean('activo')->nullable()->default(true)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flujos_aprobacion');
    }
};
