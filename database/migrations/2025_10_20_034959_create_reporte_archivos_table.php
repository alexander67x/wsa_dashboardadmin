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
        Schema::create('reporte_archivos', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_reporte')->index();
            $table->integer('archivo_id')->index();
            $table->boolean('es_foto_principal')->nullable()->default(false);
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reporte_archivos');
    }
};
