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
        Schema::create('stock_almacen', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_almacen')->index();
            $table->integer('id_material')->index();
            $table->integer('id_lote')->nullable()->index();
            $table->decimal('cantidad_disponible', 14)->nullable()->default(0);
            $table->decimal('cantidad_reservada', 14)->nullable()->default(0);
            $table->decimal('cantidad_minima_alerta', 14)->nullable()->default(0);
            $table->string('ubicacion_fisica')->nullable()->comment('estante, pasillo, sector');
            $table->timestamp('updated_at')->nullable()->useCurrent()->index();

            $table->index(['id_almacen', 'id_material'], 'idx_stock_almacen_id_almacen_material');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_almacen');
    }
};
