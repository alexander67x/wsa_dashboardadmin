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
        Schema::table('lote_material', function (Blueprint $table) {
            if (! Schema::hasColumn('lote_material', 'updated_at')) {
                $table->timestamp('updated_at')
                    ->nullable()
                    ->useCurrent()
                    ->useCurrentOnUpdate()
                    ->after('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lote_material', function (Blueprint $table) {
            if (Schema::hasColumn('lote_material', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};
