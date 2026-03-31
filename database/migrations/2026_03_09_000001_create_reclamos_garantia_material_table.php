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
        Schema::create('reclamos_garantia_material', function (Blueprint $table) {
            $table->integer('id_reclamo', true);
            $table->integer('id_stock')->index();
            $table->integer('id_material')->index();
            $table->integer('id_almacen')->index();
            $table->integer('id_lote')->nullable()->index();
            $table->string('cod_proy')->nullable()->index();
            $table->decimal('cantidad_reclamada', 14);
            $table->enum('estado', ['abierto', 'aprobado', 'rechazado', 'finalizado'])
                ->default('abierto')
                ->index();
            $table->enum('resultado_final', ['devuelto_stock', 'baja_definitiva'])->nullable()->index();
            $table->text('motivo_dano');
            $table->text('observaciones')->nullable();
            $table->integer('creado_por')->index();
            $table->integer('aprobado_por')->nullable()->index();
            $table->integer('finalizado_por')->nullable()->index();
            $table->timestamp('fecha_aprobacion')->nullable()->index();
            $table->timestamp('fecha_finalizacion')->nullable()->index();
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reclamos_garantia_material');
    }
};
