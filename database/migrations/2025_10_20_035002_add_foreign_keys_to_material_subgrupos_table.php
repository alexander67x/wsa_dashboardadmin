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
        Schema::table('material_subgrupos', function (Blueprint $table) {
            $table->foreign(['id_grupo'], 'fk_material_subgrupos_id_grupo')->references(['id_grupo'])->on('material_grupos')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_subgrupos', function (Blueprint $table) {
            $table->dropForeign('fk_material_subgrupos_id_grupo');
        });
    }
};
