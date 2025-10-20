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
        Schema::create('historial_estados_proyecto', function (Blueprint $table) {
            $table->integer('id_historial', true);
            $table->string('cod_proy')->index();
            $table->string('estado_anterior');
            $table->string('estado_nuevo');
            $table->timestamp('fecha_cambio')->useCurrent()->index();
            $table->text('motivo_cambio')->nullable();
            $table->integer('usuario_cambio')->index();
            $table->text('impacto_estimado')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_estados_proyecto');
    }
};
