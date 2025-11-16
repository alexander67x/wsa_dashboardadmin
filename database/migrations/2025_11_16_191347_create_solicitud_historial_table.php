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
        Schema::create('solicitud_historial', function (Blueprint $table) {
            $table->integer('id_historial', true);
            $table->integer('id_solicitud')->index();
            $table->enum('tipo_evento', [
                'creada',
                'aprobada',
                'aprobada_con_compra',
                'aprobada_solo_stock',
                'rechazada',
                'enviado',
                'entregado',
                'recibida',
                'cancelada'
            ])->index();
            $table->string('descripcion')->nullable();
            $table->text('detalles')->nullable()->comment('JSON con detalles del evento');
            $table->unsignedBigInteger('usuario_id')->index()->comment('Usuario que realizó la acción');
            $table->integer('empleado_id')->nullable()->index()->comment('Empleado asociado al usuario');
            $table->timestamp('fecha_evento')->useCurrent()->index();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['id_solicitud', 'fecha_evento'], 'idx_historial_solicitud_fecha');
            
            // Foreign keys
            $table->foreign(['id_solicitud'], 'fk_solicitud_historial_id_solicitud')
                ->references(['id_solicitud'])
                ->on('solicitudes_materiales')
                ->onUpdate('no action')
                ->onDelete('cascade');

            $table->foreign(['usuario_id'], 'fk_solicitud_historial_usuario_id')
                ->references(['id'])
                ->on('users')
                ->onUpdate('no action')
                ->onDelete('restrict');

            $table->foreign(['empleado_id'], 'fk_solicitud_historial_empleado_id')
                ->references(['cod_empleado'])
                ->on('empleados')
                ->onUpdate('no action')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_historial');
    }
};
