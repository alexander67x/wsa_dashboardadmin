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
        Schema::table('flujos_aprobacion', function (Blueprint $table) {
            $table->foreign(['empleado_aprobador'], 'fk_flujos_aprobacion_empleado_aprobador')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flujos_aprobacion', function (Blueprint $table) {
            $table->dropForeign('fk_flujos_aprobacion_empleado_aprobador');
        });
    }
};
