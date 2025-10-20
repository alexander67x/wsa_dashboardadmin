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
        Schema::create('almacenes', function (Blueprint $table) {
            $table->integer('id_almacen', true);
            $table->string('codigo_almacen')->index();
            $table->string('nombre');
            $table->text('direccion')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('pais')->nullable()->default('Bolivia');
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->enum('tipo_ubicacion', ['almacen', 'temporal'])->nullable()->default('almacen');
            $table->integer('responsable')->index();
            $table->enum('tipo', ['central', 'proyecto', 'temporal']);
            $table->string('cod_proy')->nullable()->index()->comment('null si es almacén central');
            $table->boolean('activo')->nullable()->default(true)->index();
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
            $table->timestamp('updated_at')->nullable()->useCurrent();

            $table->unique(['codigo_almacen'], 'codigo_almacen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('almacenes');
    }
};
