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
        Schema::create('lote_material', function (Blueprint $table) {
            $table->integer('id_lote', true);
            $table->integer('id_material')->index();
            $table->string('numero_lote')->nullable();
            $table->string('numero_serie')->nullable();
            $table->text('especificaciones')->nullable();
            $table->timestamp('fecha_ingreso')->nullable()->useCurrent();
            $table->date('fecha_vencimiento')->nullable()->index();
            $table->integer('garantia_dias')->nullable();
            $table->integer('cod_proveedor')->nullable()->index();
            $table->enum('estado_lote', ['disponible', 'reservado', 'agotado', 'vencido'])->nullable()->default('disponible')->index();
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lote_material');
    }
};
