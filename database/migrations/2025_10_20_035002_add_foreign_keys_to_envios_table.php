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
        Schema::table('envios', function (Blueprint $table) {
            $table->foreign(['cod_proy'], 'fk_envios_cod_proy')->references(['cod_proy'])->on('proyectos')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['enviado_por'], 'fk_envios_enviado_por')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_almacen_origen'], 'fk_envios_id_almacen_origen')->references(['id_almacen'])->on('almacenes')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_solicitud'], 'fk_envios_id_solicitud')->references(['id_solicitud'])->on('solicitudes_materiales')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('envios', function (Blueprint $table) {
            $table->dropForeign('fk_envios_cod_proy');
            $table->dropForeign('fk_envios_enviado_por');
            $table->dropForeign('fk_envios_id_almacen_origen');
            $table->dropForeign('fk_envios_id_solicitud');
        });
    }
};
