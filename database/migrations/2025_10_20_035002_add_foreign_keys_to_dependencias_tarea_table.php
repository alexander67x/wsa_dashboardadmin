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
        Schema::table('dependencias_tarea', function (Blueprint $table) {
            $table->foreign(['depende_de_id'], 'fk_dependencias_tarea_depende_de_id')->references(['id_tarea'])->on('tareas')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['tarea_id'], 'fk_dependencias_tarea_tarea_id')->references(['id_tarea'])->on('tareas')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dependencias_tarea', function (Blueprint $table) {
            $table->dropForeign('fk_dependencias_tarea_depende_de_id');
            $table->dropForeign('fk_dependencias_tarea_tarea_id');
        });
    }
};
