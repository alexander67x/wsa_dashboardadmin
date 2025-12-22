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
        Schema::table('material_deliveries', function (Blueprint $table) {
            $table->string('foto_recepcion_url')
                ->nullable()
                ->after('fecha_recepcion')
                ->comment('URL de la foto tomada en obra al recibir el material');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_deliveries', function (Blueprint $table) {
            $table->dropColumn('foto_recepcion_url');
        });
    }
};
