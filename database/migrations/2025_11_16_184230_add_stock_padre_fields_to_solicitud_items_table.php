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
        Schema::table('solicitud_items', function (Blueprint $table) {
            $table->decimal('cantidad_disponible_padre', 14)->nullable()->after('cantidad_solicitada')->comment('Cantidad disponible en almacén padre');
            $table->decimal('cantidad_faltante', 14)->nullable()->after('cantidad_disponible_padre')->comment('Cantidad que necesita comprarse');
            $table->boolean('requiere_compra')->default(false)->after('cantidad_faltante')->comment('Indica si requiere compra de material');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitud_items', function (Blueprint $table) {
            $table->dropColumn(['cantidad_disponible_padre', 'cantidad_faltante', 'requiere_compra']);
        });
    }
};
