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
        Schema::table('configuracion_alertas', function (Blueprint $table) {
            $table->foreign(['cod_empleado'], 'fk_configuracion_alertas_cod_empleado')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracion_alertas', function (Blueprint $table) {
            $table->dropForeign('fk_configuracion_alertas_cod_empleado');
        });
    }
};
