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
        Schema::create('materiales', function (Blueprint $table) {
            $table->integer('id_material', true);
            $table->string('codigo_producto')->unique('codigo_producto');
            $table->string('nombre_producto');
            $table->integer('id_subgrupo')->index();
            $table->string('unidad_medida');
            $table->decimal('costo_unitario_promedio_bs', 14)->nullable()->default(0);
            $table->decimal('equivalencia', 14)->nullable()->comment('ej: 1 caja = 12 unidades');
            $table->string('unidad_equivalencia')->nullable();
            $table->decimal('stock_minimo', 14)->nullable()->default(0);
            $table->decimal('stock_maximo', 14)->nullable();
            $table->enum('criticidad', ['critico', 'no_critico'])->nullable()->default('no_critico')->index();
            $table->boolean('activo')->nullable()->default(true)->index();
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
            $table->timestamp('updated_at')->nullable()->useCurrent();

            $table->index(['codigo_producto']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materiales');
    }
};
