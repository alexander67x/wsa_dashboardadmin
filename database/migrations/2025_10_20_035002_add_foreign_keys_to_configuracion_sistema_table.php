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
        Schema::table('configuracion_sistema', function (Blueprint $table) {
            $table->foreign(['updated_by'], 'fk_configuracion_sistema_updated_by')->references(['cod_empleado'])->on('empleados')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracion_sistema', function (Blueprint $table) {
            $table->dropForeign('fk_configuracion_sistema_updated_by');
        });
    }
};
