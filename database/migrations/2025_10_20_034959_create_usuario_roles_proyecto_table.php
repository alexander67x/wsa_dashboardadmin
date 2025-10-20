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
        Schema::create('usuario_roles_proyecto', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('cod_proy')->index();
            $table->integer('cod_empleado')->index();
            $table->integer('role_id')->index();
            $table->timestamp('fecha_asignacion')->nullable()->useCurrent()->index();
            $table->timestamp('fecha_fin_asignacion')->nullable();
            $table->boolean('activo')->nullable()->default(true)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_roles_proyecto');
    }
};
