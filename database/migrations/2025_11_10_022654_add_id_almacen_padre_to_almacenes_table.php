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
        Schema::table('almacenes', function (Blueprint $table) {
            $table->integer('id_almacen_padre')->nullable()->after('tipo')->index()->comment('null si es almacén central');
        });

        Schema::table('almacenes', function (Blueprint $table) {
            $table->foreign(['id_almacen_padre'], 'fk_almacenes_id_almacen_padre')
                ->references(['id_almacen'])
                ->on('almacenes')
                ->onUpdate('no action')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('almacenes', function (Blueprint $table) {
            $table->dropForeign('fk_almacenes_id_almacen_padre');
            $table->dropColumn('id_almacen_padre');
        });
    }
};
