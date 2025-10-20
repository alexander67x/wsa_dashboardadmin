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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->integer('id_mov', true);
            $table->string('numero_movimiento')->index();
            $table->integer('id_material')->index();
            $table->integer('id_lote')->nullable()->index();
            $table->integer('id_almacen_origen')->nullable()->index();
            $table->integer('id_almacen_destino')->nullable()->index();
            $table->enum('tipo_movimiento', ['ingreso', 'salida', 'transferencia', 'ajuste', 'reserva', 'liberacion', 'devolucion'])->index();
            $table->decimal('cantidad', 14);
            $table->string('referencia')->nullable()->comment('número de solicitud/OC/etc.');
            $table->text('motivo')->nullable();
            $table->decimal('costo_unitario', 14)->nullable();
            $table->decimal('valor_total', 14)->nullable();
            $table->timestamp('fecha_movimiento')->nullable()->useCurrent()->index();
            $table->integer('registrado_por')->index();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->unique(['numero_movimiento'], 'numero_movimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
