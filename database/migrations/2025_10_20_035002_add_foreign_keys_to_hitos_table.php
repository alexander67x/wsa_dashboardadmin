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
        Schema::table('hitos', function (Blueprint $table) {
            $table->foreign(['cod_proy'], 'fk_hitos_cod_proy')->references(['cod_proy'])->on('proyectos')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['creado_por'], 'fk_hitos_creado_por')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_fase'], 'fk_hitos_id_fase')->references(['id_fase'])->on('fases')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hitos', function (Blueprint $table) {
            $table->dropForeign('fk_hitos_cod_proy');
            $table->dropForeign('fk_hitos_creado_por');
            $table->dropForeign('fk_hitos_id_fase');
        });
    }
};
