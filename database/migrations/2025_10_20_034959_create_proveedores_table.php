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
        Schema::create('proveedores', function (Blueprint $table) {
            $table->integer('cod_proveedor', true);
            $table->string('nombre')->index();
            $table->string('contacto')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable()->index();
            $table->text('direccion')->nullable();
            $table->enum('tipo', ['local', 'internacional']);
            $table->string('especialidad')->nullable();
            $table->decimal('calificacion', 3)->nullable()->comment('1-5 estrellas');
            $table->boolean('activo')->nullable()->default(true)->index();
            $table->integer('tiempo_entrega')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
