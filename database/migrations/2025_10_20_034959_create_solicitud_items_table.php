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
        Schema::create('solicitud_items', function (Blueprint $table) {
            $table->integer('id_item', true);
            $table->integer('id_solicitud')->index();
            $table->integer('id_material')->index();
            $table->integer('id_lote')->nullable()->index();
            $table->decimal('cantidad_solicitada', 14);
            $table->decimal('cantidad_aprobada', 14)->nullable();
            $table->decimal('cantidad_entregada', 14)->nullable()->default(0);
            $table->string('unidad');
            $table->text('justificacion')->nullable();
            $table->text('observaciones')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_items');
    }
};
