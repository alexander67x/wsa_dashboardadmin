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
        Schema::table('proyectos', function (Blueprint $table) {
            $table->foreign(['cod_cliente'], 'fk_proyectos_cod_cliente')->references(['cod_cliente'])->on('clientes')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['responsable_proyecto'], 'fk_proyectos_responsable_proyecto')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['supervisor_obra'], 'fk_proyectos_supervisor_obra')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropForeign('fk_proyectos_cod_cliente');
            $table->dropForeign('fk_proyectos_responsable_proyecto');
            $table->dropForeign('fk_proyectos_supervisor_obra');
        });
    }
};
