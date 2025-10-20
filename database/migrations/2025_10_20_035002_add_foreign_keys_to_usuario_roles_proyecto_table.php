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
        Schema::table('usuario_roles_proyecto', function (Blueprint $table) {
            $table->foreign(['cod_empleado'], 'fk_usuario_roles_proyecto_cod_empleado')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['cod_proy'], 'fk_usuario_roles_proyecto_cod_proy')->references(['cod_proy'])->on('proyectos')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['role_id'], 'fk_usuario_roles_proyecto_role_id')->references(['id_role'])->on('roles')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuario_roles_proyecto', function (Blueprint $table) {
            $table->dropForeign('fk_usuario_roles_proyecto_cod_empleado');
            $table->dropForeign('fk_usuario_roles_proyecto_cod_proy');
            $table->dropForeign('fk_usuario_roles_proyecto_role_id');
        });
    }
};
