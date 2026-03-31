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
        Schema::create('bajas_garantia_material', function (Blueprint $table) {
            $table->integer('id_baja', true);
            $table->integer('id_reclamo')->unique();
            $table->integer('id_stock')->index();
            $table->integer('id_material')->index();
            $table->integer('id_almacen')->index();
            $table->integer('id_lote')->nullable()->index();
            $table->string('cod_proy')->nullable()->index();
            $table->decimal('cantidad_baja', 14);
            $table->text('motivo')->nullable();
            $table->text('observaciones')->nullable();
            $table->integer('registrado_por')->nullable()->index();
            $table->timestamp('fecha_baja')->useCurrent()->index();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bajas_garantia_material');
    }
};

