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
        Schema::create('proyectos', function (Blueprint $table) {
            $table->string('cod_proy')->primary();
            $table->integer('cod_cliente')->index();
            $table->string('nombre_ubicacion');
            $table->text('direccion')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('pais')->nullable()->default('Bolivia');
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->enum('tipo_ubicacion', ['obra', 'cliente'])->nullable()->default('obra');
            $table->date('fecha_inicio')->index();
            $table->date('fecha_fin_estimada')->nullable();
            $table->date('fecha_fin_real')->nullable();
            $table->enum('estado', ['activo', 'completado', 'cancelado', 'pausado'])->nullable()->default('activo')->index();
            $table->text('descripcion')->nullable();
            $table->decimal('avance_financiero', 14)->nullable();
            $table->decimal('gasto_real', 14)->nullable();
            $table->integer('rentabilidad')->nullable();
            $table->integer('responsable_proyecto')->index()->comment('Empleado responsable principal');
            $table->integer('supervisor_obra')->nullable()->index()->comment('Supervisor presente en obra');
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->softDeletes();

            $table->index(['cod_cliente', 'estado'], 'idx_proyectos_cod_cliente_estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
