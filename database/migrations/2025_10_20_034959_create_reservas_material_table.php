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
        Schema::create('reservas_material', function (Blueprint $table) {
            $table->integer('id_reserva', true);
            $table->integer('id_solicitud')->index();
            $table->integer('id_material')->index();
            $table->integer('id_almacen')->index();
            $table->integer('id_lote')->nullable()->index();
            $table->decimal('cantidad_reservada', 14);
            $table->timestamp('fecha_reserva')->nullable()->useCurrent()->index();
            $table->timestamp('fecha_vencimiento_reserva')->nullable();
            $table->enum('estado', ['activa', 'utilizada', 'vencida', 'cancelada'])->nullable()->default('activa')->index();
            $table->integer('reservado_por')->index('fk_reservas_material_reservado_por');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas_material');
    }
};
