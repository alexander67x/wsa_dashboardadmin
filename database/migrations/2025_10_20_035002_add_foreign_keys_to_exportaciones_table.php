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
        Schema::table('exportaciones', function (Blueprint $table) {
            $table->foreign(['cod_proy'], 'fk_exportaciones_cod_proy')->references(['cod_proy'])->on('proyectos')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['generado_por'], 'fk_exportaciones_generado_por')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exportaciones', function (Blueprint $table) {
            $table->dropForeign('fk_exportaciones_cod_proy');
            $table->dropForeign('fk_exportaciones_generado_por');
        });
    }
};
