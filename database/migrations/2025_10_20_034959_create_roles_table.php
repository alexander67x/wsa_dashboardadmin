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
        Schema::create('roles', function (Blueprint $table) {
            $table->integer('id_role', true);
            $table->string('nombre')->index();
            $table->text('descripcion')->nullable();
            $table->boolean('es_global')->nullable()->default(false)->index();
            $table->boolean('puede_aprobar_solicitudes')->nullable()->default(false);
            $table->boolean('puede_generar_reportes')->nullable()->default(false);
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
