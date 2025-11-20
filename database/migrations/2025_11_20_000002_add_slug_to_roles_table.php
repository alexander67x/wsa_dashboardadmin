<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (! Schema::hasColumn('roles', 'slug')) {
                $table->string('slug')->nullable()->after('id_role');
            }
        });

        $roles = Role::all();
        foreach ($roles as $role) {
            $role->slug = $role->slug ?: Str::slug($role->nombre);
            $role->save();
        }

        Schema::table('roles', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'slug')) {
                $table->dropUnique('roles_slug_unique');
                $table->dropColumn('slug');
            }
        });
    }
};
