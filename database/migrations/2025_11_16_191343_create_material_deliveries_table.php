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
        Schema::create('material_deliveries', function (Blueprint $table) {
            $table->integer('id_entrega', true);
            $table->string('numero_entrega')->unique()->index();
            $table->integer('id_solicitud')->index();
            $table->integer('id_item')->index();
            $table->integer('id_material')->index();
            $table->integer('id_lote')->nullable()->index();
            $table->integer('id_almacen_origen')->index()->comment('Almacén padre que entrega');
            $table->integer('id_almacen_destino')->index()->comment('Almacén del proyecto que recibe');
            $table->decimal('cantidad_entregada', 14);
            $table->decimal('cantidad_aprobada', 14)->comment('Cantidad aprobada del item');
            $table->enum('tipo_entrega', ['completa', 'parcial'])->default('completa');
            $table->text('motivo_parcial')->nullable()->comment('Motivo si la entrega es parcial');
            $table->timestamp('fecha_entrega')->useCurrent()->index();
            $table->integer('entregado_por')->index()->comment('Empleado que realizó la entrega');
            $table->integer('recibido_por')->nullable()->index()->comment('Empleado que recibió la entrega');
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['preparado', 'en_transito', 'entregado', 'recibido'])->default('preparado')->index();
            $table->timestamp('fecha_recepcion')->nullable();
            $table->timestamps();

            $table->index(['id_solicitud', 'id_item'], 'idx_deliveries_solicitud_item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_deliveries');
    }
};
