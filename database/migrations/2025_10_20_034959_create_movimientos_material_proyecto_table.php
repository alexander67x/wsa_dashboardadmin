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
        Schema::create('movimientos_material_proyecto', function (Blueprint $table) {
            $table->integer('id_movimiento', true);
            $table->string('cod_proy')->index();
            $table->integer('id_tarea')->nullable()->index();
            $table->integer('id_material')->index();
            $table->integer('id_lote')->nullable()->index();
            $table->decimal('cantidad', 14);
            $table->enum('tipo_movimiento', ['asignado', 'consumido', 'devuelto', 'desperdicio'])->index();
            $table->decimal('costo_unitario', 14)->nullable();
            $table->decimal('valor_total', 14)->nullable();
            $table->timestamp('fecha_movimiento')->nullable()->useCurrent()->index();
            $table->integer('responsable')->index();
            $table->text('observaciones')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_material_proyecto');
    }
};
