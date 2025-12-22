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
        Schema::create('reunion_clientes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('cliente_id')->index();
            $table->timestamp('fecha_reunion')->index();
            $table->string('tipo')->nullable()->comment('Ej: Kickoff, Seguimiento, Cierre');
            $table->string('tema')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('acuerdos')->nullable();
            $table->timestamp('proximo_seguimiento')->nullable()->index();
            $table->string('responsable_interno')->nullable();
            $table->string('medio')->nullable()->comment('Presencial, Videollamada, Teléfono, etc.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reunion_clientes');
    }
};
