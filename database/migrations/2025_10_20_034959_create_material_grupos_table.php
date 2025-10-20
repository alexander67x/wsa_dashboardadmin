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
        Schema::create('material_grupos', function (Blueprint $table) {
            $table->integer('id_grupo', true);
            $table->string('codigo_grupo')->unique('codigo_grupo');
            $table->string('nombre')->index();
            $table->text('descripcion')->nullable();

            $table->index(['codigo_grupo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_grupos');
    }
};
