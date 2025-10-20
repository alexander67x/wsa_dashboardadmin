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
        Schema::create('garantias_post_venta', function (Blueprint $table) {
            $table->integer('id_garantia', true);
            $table->string('cod_proy')->index();
            $table->enum('tipo_garantia', ['tecnica', 'financiera', 'cumplimiento']);
            $table->decimal('monto_garantia', 14);
            $table->date('fecha_inicio_garantia')->index();
            $table->date('fecha_fin_garantia');
            $table->enum('estado_garantia', ['activa', 'ejecutada', 'liberada', 'vencida'])->default('activa')->index();
            $table->string('entidad_garante')->nullable();
            $table->string('numero_poliza')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent()->index();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garantias_post_venta');
    }
};
