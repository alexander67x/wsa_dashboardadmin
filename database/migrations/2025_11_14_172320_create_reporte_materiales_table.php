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
        Schema::create('reporte_materiales', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('id_reporte')->index();
            $table->integer('id_material')->index();
            $table->decimal('cantidad_usada', 14, 2);
            $table->string('unidad_medida')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            
            $table->foreign('id_reporte', 'fk_reporte_materiales_id_reporte')
                ->references('id_reporte')
                ->on('reportes_avance_tarea')
                ->onUpdate('no action')
                ->onDelete('cascade');
            
            $table->foreign('id_material', 'fk_reporte_materiales_id_material')
                ->references('id_material')
                ->on('materiales')
                ->onUpdate('no action')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reporte_materiales');
    }
};
