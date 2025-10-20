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
        Schema::create('configuracion_sistema', function (Blueprint $table) {
            $table->integer('id_config', true);
            $table->string('clave')->unique('clave');
            $table->text('valor');
            $table->text('descripcion')->nullable();
            $table->enum('tipo_dato', ['string', 'integer', 'decimal', 'boolean', 'json'])->nullable()->default('string');
            $table->string('categoria')->nullable()->index()->comment('notificaciones, reportes, inventario, etc.');
            $table->boolean('modificable')->nullable()->default(true);
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->integer('updated_by')->nullable()->index();

            $table->index(['clave']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_sistema');
    }
};
