<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $config = config('roles');

        if (! is_array($config) || empty($config['roles'])) {
            $this->command?->warn('⚠️ Configuración de roles no encontrada. No se generaron roles/permisos.');
            return;
        }

        $permissionIds = [];
        foreach ($config['permissions'] ?? [] as $code => $meta) {
            $permission = Permission::updateOrCreate(
                ['codigo' => $code],
                [
                    'descripcion' => $meta['label'] ?? null,
                    'modulo' => $meta['module'] ?? null,
                ]
            );

            $permissionIds[$code] = $permission->id_permission;
        }

        foreach ($config['roles'] as $slug => $roleConfig) {
            $permissions = collect($roleConfig['permissions'] ?? []);

            $role = Role::updateOrCreate(
                ['slug' => $slug],
                [
                    'nombre' => $roleConfig['name'],
                    'descripcion' => $roleConfig['description'] ?? null,
                    'es_global' => (bool) ($roleConfig['global'] ?? false),
                    'puede_aprobar_solicitudes' => $permissions->contains('materials.requests.approve'),
                    'puede_generar_reportes' => $permissions->contains(fn ($code) => str_starts_with($code, 'reports.')),
                ]
            );

            $rolePermissionIds = $permissions
                ->map(fn ($code) => $permissionIds[$code] ?? null)
                ->filter()
                ->values()
                ->all();

            $role->permissions()->sync($rolePermissionIds);

            $this->command?->info("✅ Rol sincronizado: {$role->nombre}");
        }
    }
}
