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
        Schema::create('clientes', function (Blueprint $table) {
            $table->integer('cod_cliente', true);
            $table->string('nombre_cliente')->index();
            $table->string('industria')->nullable();
            $table->string('contacto_principal')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('telefono')->nullable();
            $table->text('direccion')->nullable();
            $table->boolean('activo')->nullable()->default(true)->index();
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->softDeletes();

            $table->unique(['email'], 'email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
