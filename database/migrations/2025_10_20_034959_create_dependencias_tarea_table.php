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
        Schema::create('dependencias_tarea', function (Blueprint $table) {
            $table->integer('id_dependencia', true);
            $table->integer('tarea_id')->index();
            $table->integer('depende_de_id')->index();
            $table->enum('tipo_dependencia', ['FS', 'FF', 'SS', 'SF'])->nullable()->default('FS')->comment('Finish-Start, Finish-Finish, Start-Start, Start-Finish');
            $table->integer('lag_dias')->nullable()->default(0)->comment('días de desfase');
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dependencias_tarea');
    }
};
