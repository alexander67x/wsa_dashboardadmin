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
        Schema::table('kanban_boards', function (Blueprint $table) {
            // Enforce one board per proyecto
            $table->unique('cod_proy', 'uq_kanban_boards_cod_proy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kanban_boards', function (Blueprint $table) {
            $table->dropUnique('uq_kanban_boards_cod_proy');
        });
    }
};


