<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reunion_clientes', function (Blueprint $table) {
            if (! Schema::hasColumn('reunion_clientes', 'responsable_interno_id')) {
                $table->integer('responsable_interno_id')
                    ->nullable()
                    ->after('proximo_seguimiento')
                    ->index();

                $table->foreign('responsable_interno_id', 'fk_reunion_responsable_empleado')
                    ->references('cod_empleado')
                    ->on('empleados')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('reunion_clientes', function (Blueprint $table) {
            if (Schema::hasColumn('reunion_clientes', 'responsable_interno_id')) {
                $table->dropForeign('fk_reunion_responsable_empleado');
                $table->dropColumn('responsable_interno_id');
            }
        });
    }
};
