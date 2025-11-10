<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MaterialGrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grupos = [
            [
                'codigo_grupo' => 'GRP-001',
                'nombre' => 'Cámaras de Seguridad',
                'descripcion' => 'Sistemas de videovigilancia y cámaras IP',
            ],
            [
                'codigo_grupo' => 'GRP-002',
                'nombre' => 'Sensores y Detectores',
                'descripcion' => 'Sensores de movimiento, humo, rotura de vidrios, etc.',
            ],
            [
                'codigo_grupo' => 'GRP-003',
                'nombre' => 'Control de Acceso',
                'descripcion' => 'Sistemas biométricos, lectores de tarjetas, torniquetes',
            ],
            [
                'codigo_grupo' => 'GRP-004',
                'nombre' => 'Cableado y Redes',
                'descripcion' => 'Cables, conectores y accesorios para instalaciones de seguridad',
            ],
            [
                'codigo_grupo' => 'GRP-005',
                'nombre' => 'Alarmas y Sirenas',
                'descripcion' => 'Sistemas de alarma y notificación',
            ],
            [
                'codigo_grupo' => 'GRP-006',
                'nombre' => 'Cercos Eléctricos',
                'descripcion' => 'Sistemas de protección perimetral',
            ],
            [
                'codigo_grupo' => 'GRP-007',
                'nombre' => 'Herramientas y Equipos',
                'descripcion' => 'Herramientas para instalación y mantenimiento',
            ]
        ];

        $this->command->info("📦 Creando grupos de materiales...");

        foreach ($grupos as $grupo) {
            DB::table('material_grupos')->updateOrInsert(
                ['codigo_grupo' => $grupo['codigo_grupo']],
                $grupo
            );
        }

        $this->command->info("✅ " . count($grupos) . " grupos de materiales creados/verificados");
    }
}

