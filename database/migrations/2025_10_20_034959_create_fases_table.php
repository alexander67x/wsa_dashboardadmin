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
        Schema::create('fases', function (Blueprint $table) {
            $table->integer('id_fase', true);
            $table->string('cod_proy')->index();
            $table->string('nombre_fase');
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio')->nullable()->index();
            $table->date('fecha_fin')->nullable();
            $table->integer('orden')->index();
            $table->enum('estado', ['planificada', 'en_ejecucion', 'finalizada', 'pausada'])->nullable()->default('planificada')->index();
            $table->decimal('porcentaje_avance', 5)->nullable()->default(0);
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
        Schema::dropIfExists('fases');
    }
};
