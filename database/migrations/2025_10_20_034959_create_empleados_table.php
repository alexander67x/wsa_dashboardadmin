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
        Schema::create('empleados', function (Blueprint $table) {
            $table->integer('cod_empleado', true);
            $table->string('cod_proy')->nullable()->index();
            $table->string('nombre_completo')->index();
            $table->string('cargo')->nullable();
            $table->string('departamento')->nullable();
            $table->string('email')->nullable()->unique('email');
            $table->string('telefono')->nullable();
            $table->date('fecha_ingreso')->nullable()->index();
            $table->boolean('activo')->nullable()->default(true)->index();
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->softDeletes();

            $table->index(['email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
