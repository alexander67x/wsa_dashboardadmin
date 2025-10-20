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
        Schema::create('material_subgrupos', function (Blueprint $table) {
            $table->integer('id_subgrupo', true);
            $table->integer('id_grupo')->index();
            $table->string('codigo_subgrupo')->unique('codigo_subgrupo');
            $table->string('nombre')->index();
            $table->text('descripcion')->nullable();

            $table->index(['codigo_subgrupo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_subgrupos');
    }
};
