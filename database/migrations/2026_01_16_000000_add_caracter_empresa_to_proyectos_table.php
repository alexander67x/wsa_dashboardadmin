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
        Schema::table('proyectos', function (Blueprint $table) {
            if (! Schema::hasColumn('proyectos', 'caracter_empresa')) {
                $table->enum('caracter_empresa', ['publico', 'privado'])
                    ->nullable()
                    ->default('publico')
                    ->after('estado');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            if (Schema::hasColumn('proyectos', 'caracter_empresa')) {
                $table->dropColumn('caracter_empresa');
            }
        });
    }
};

