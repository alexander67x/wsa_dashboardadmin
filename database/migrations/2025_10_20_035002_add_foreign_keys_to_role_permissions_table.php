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
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->foreign(['permission_id'], 'fk_role_permissions_permission_id')->references(['id_permission'])->on('permissions')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['role_id'], 'fk_role_permissions_role_id')->references(['id_role'])->on('roles')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropForeign('fk_role_permissions_permission_id');
            $table->dropForeign('fk_role_permissions_role_id');
        });
    }
};
