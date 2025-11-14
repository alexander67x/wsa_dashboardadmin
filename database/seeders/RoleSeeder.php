<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear rol de Administrador
        Role::firstOrCreate(
            ['nombre' => 'Administrador'],
            [
                'descripcion' => 'Administrador del sistema con acceso completo',
                'es_global' => true,
                'puede_aprobar_solicitudes' => true,
                'puede_generar_reportes' => true,
            ]
        );

        // Crear rol de Encargado de obra
        Role::firstOrCreate(
            ['nombre' => 'Encargado de obra'],
            [
                'descripcion' => 'Encargado de supervisar y gestionar obras',
                'es_global' => false,
                'puede_aprobar_solicitudes' => true,
                'puede_generar_reportes' => true,
            ]
        );

        $this->command->info('✅ Roles creados: Administrador y Encargado de obra');
    }
}
