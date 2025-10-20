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
        Schema::create('permissions', function (Blueprint $table) {
            $table->integer('id_permission', true);
            $table->string('codigo')->unique('codigo')->comment('ej: tarea.create, solicitud.approve');
            $table->text('descripcion')->nullable();
            $table->string('modulo')->nullable()->index()->comment('proyectos, inventario, reportes, etc.');

            $table->index(['codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
