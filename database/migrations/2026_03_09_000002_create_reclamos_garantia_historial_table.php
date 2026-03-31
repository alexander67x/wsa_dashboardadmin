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
        Schema::create('reclamos_garantia_historial', function (Blueprint $table) {
            $table->integer('id_historial', true);
            $table->integer('id_reclamo')->index();
            $table->string('estado_anterior')->nullable();
            $table->string('estado_nuevo');
            $table->string('accion', 120)->nullable();
            $table->text('comentario')->nullable();
            $table->integer('creado_por')->nullable()->index();
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reclamos_garantia_historial');
    }
};
