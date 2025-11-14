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
        Schema::table('empleados', function (Blueprint $table) {
            $table->integer('id_role')->nullable()->after('email')->index();
            $table->unsignedBigInteger('user_id')->nullable()->after('id_role')->unique();
            
            $table->foreign('id_role', 'fk_empleados_id_role')
                ->references('id_role')
                ->on('roles')
                ->onUpdate('no action')
                ->onDelete('set null');
                
            $table->foreign('user_id', 'fk_empleados_user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('no action')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropForeign('fk_empleados_user_id');
            $table->dropForeign('fk_empleados_id_role');
            $table->dropColumn(['id_role', 'user_id']);
        });
    }
};
