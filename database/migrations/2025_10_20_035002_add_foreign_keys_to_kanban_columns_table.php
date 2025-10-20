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
        Schema::table('kanban_columns', function (Blueprint $table) {
            $table->foreign(['board_id'], 'fk_kanban_columns_board_id')->references(['id_board'])->on('kanban_boards')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kanban_columns', function (Blueprint $table) {
            $table->dropForeign('fk_kanban_columns_board_id');
        });
    }
};
