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
        Schema::create('envios', function (Blueprint $table) {
            $table->integer('id_envio', true);
            $table->string('numero_envio')->index();
            $table->integer('id_solicitud')->index();
            $table->integer('id_almacen_origen')->index();
            $table->string('cod_proy')->index();
            $table->timestamp('fecha_envio')->index();
            $table->time('hora_envio')->nullable();
            $table->enum('estado', ['preparado', 'en_transito', 'entregado', 'parcial', 'cancelado'])->nullable()->default('preparado')->index();
            $table->text('observaciones')->nullable();
            $table->integer('enviado_por')->index();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->unique(['numero_envio'], 'numero_envio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('envios');
    }
};
