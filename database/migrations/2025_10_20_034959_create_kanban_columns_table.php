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
        Schema::create('kanban_columns', function (Blueprint $table) {
            $table->integer('id_column', true);
            $table->integer('board_id')->index();
            $table->string('nombre');
            $table->integer('orden')->index();
            $table->integer('wip_limit')->nullable();
            $table->boolean('es_entrada')->nullable()->default(false);
            $table->boolean('es_salida')->nullable()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanban_columns');
    }
};
