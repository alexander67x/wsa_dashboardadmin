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
        Schema::table('cumplimiento_contratos', function (Blueprint $table) {
            $table->foreign(['cod_proy'], 'fk_cumplimiento_contratos_cod_proy')->references(['cod_proy'])->on('proyectos')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['evaluador'], 'fk_cumplimiento_contratos_evaluador')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cumplimiento_contratos', function (Blueprint $table) {
            $table->dropForeign('fk_cumplimiento_contratos_cod_proy');
            $table->dropForeign('fk_cumplimiento_contratos_evaluador');
        });
    }
};
