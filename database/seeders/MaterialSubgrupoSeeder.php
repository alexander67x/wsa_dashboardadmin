<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MaterialSubgrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si existen grupos; si no, crearlos
        $this->call([MaterialGrupoSeeder::class]);
        
        // Obtener referencias a los grupos
        $grupoCamaras = DB::table('material_grupos')->where('codigo_grupo', 'GRP-001')->first();
        $grupoSensores = DB::table('material_grupos')->where('codigo_grupo', 'GRP-002')->first();
        $grupoControlAcceso = DB::table('material_grupos')->where('codigo_grupo', 'GRP-003')->first();
        $grupoCableado = DB::table('material_grupos')->where('codigo_grupo', 'GRP-004')->first();
        $grupoAlarmas = DB::table('material_grupos')->where('codigo_grupo', 'GRP-005')->first();
        $grupoCercos = DB::table('material_grupos')->where('codigo_grupo', 'GRP-006')->first();
        $grupoHerramientas = DB::table('material_grupos')->where('codigo_grupo', 'GRP-007')->first();

        // Grupo por defecto en caso de que falle alguna referencia
        $grupoDefault = $grupoCamaras;

        $subgrupos = [
            // Cámaras de Seguridad
            [
                'id_grupo' => $grupoCamaras->id_grupo ?? $grupoDefault->id_grupo,
                'codigo_subgrupo' => 'SUB-001',
                'nombre' => 'Cámaras IP',
                'descripcion' => 'Cámaras de red para vigilancia IP',
            ],
            [
                'id_grupo' => $grupoCamaras->id_grupo ?? $grupoDefault->id_grupo,
                'codigo_subgrupo' => 'SUB-002',
                'nombre' => 'Cámaras Analógicas',
                'descripcion' => 'Cámaras de seguridad analógicas',
            ],
            [
                'id_grupo' => $grupoCamaras->id_grupo ?? $grupoDefault->id_grupo,
                'codigo_subgrupo' => 'SUB-003',
                'nombre' => 'DVR / NVR',
                'descripcion' => 'Grabadores de video digital y de red',
            ],
            
            // Sensores y Detectores
            [
                'id_grupo' => $grupoSensores->id_grupo ?? $grupoDefault->id_grupo,
                'codigo_subgrupo' => 'SUB-004',
                'nombre' => 'Sensores de Movimiento',
                'descripcion' => 'Detectores de movimiento PIR y otros',
            ],
            [
                'id_grupo' => $grupoSensores->id_grupo ?? $grupoDefault->id_grupo,
                'codigo_subgrupo' => 'SUB-005',
                'nombre' => 'Sensores de Humo y Calor',
                'descripcion' => 'Detección de incendios',
            ],
            [
                'id_grupo' => $grupoSensores->id_grupo ?? $grupoDefault->id_grupo,
                'codigo_subgrupo' => 'SUB-006',
                'nombre' => 'Sensores de Rotura',
                'descripcion' => 'Detectores de rotura de vidrios',
            ],
            
            // Control de Acceso
            [
                'id_grupo' => $grupoControlAcceso->id_grupo ?? $grupoDefault->id_grupo,
                'codigo_subgrupo' => 'SUB-007',
                'nombre' => 'Lectores Biométricos',
                'descripcion' => 'Lectores de huella, rostro, iris',
            ],
            [
                'id_grupo' => $grupoControlAcceso->id_grupo ?? $grupoDefault->id_grupo,
                'codigo_subgrupo' => 'SUB-008',
                'nombre' => 'Lectores de Tarjetas',
                'descripcion' => 'Proximidad, banda magnética, etc.',
            ],
            [
                'id_grupo' => $grupoControlAcceso->id_grupo ?? $grupoDefault->id_grupo,
                'codigo_subgrupo' => 'SUB-009',
                'nombre' => 'Cerraduras Electrónicas',
                'descripcion' => 'Cerraduras controladas electrónicamente',
            ],
            
            // Cableado y Redes
            [
                'id_grupo' => $grupoCableado->id_grupo ?? $grupoDefault->id_grupo,
                'codigo_subgrupo' => 'SUB-010',
                'nombre' => 'Cable UTP/Cat6',
                'descripcion' => 'Cableado estructurado',
            ],
            [
                'id_grupo' => $grupoCableado->id_grupo ?? $grupoDefault->id_grupo,
                'codigo_subgrupo' => 'SUB-011',
                'nombre' => 'Cable Coaxial',
                'descripcion' => 'Para cámaras analógicas',
            ],
            
            // Alarmas y Sirenas
            [
                'id_grupo' => $grupoAlarmas->id_grupo ?? $grupoDefault->id_grupo,
                'codigo_subgrupo' => 'SUB-012',
                'nombre' => 'Sirenas',
                'descripcion' => 'Sirenas interiores y exteriores',
            ],
            
            // Cercos Eléctricos
            [
                'id_grupo' => $grupoCercos->id_grupo ?? $grupoDefault->id_grupo,
                'codigo_subgrupo' => 'SUB-013',
                'nombre' => 'Cercos Electrificados',
                'descripcion' => 'Sistemas de protección perimetral',
            ],
            
            // Herramientas y Equipos
            [
                'id_grupo' => $grupoHerramientas->id_grupo ?? $grupoDefault->id_grupo,
                'codigo_subgrupo' => 'SUB-014',
                'nombre' => 'Herramientas de Instalación',
                'descripcion' => 'Para instalación de sistemas de seguridad',
            ],
            [
                'id_grupo' => $grupoHerramientas->id_grupo ?? $grupoDefault->id_grupo,
                'codigo_subgrupo' => 'SUB-015',
                'nombre' => 'Equipos de Prueba',
                'descripcion' => 'Probadores de red, multímetros, etc.',
            ]
        ];

        $this->command->info("📦 Creando subgrupos de materiales...");

        foreach ($subgrupos as $subgrupo) {
            DB::table('material_subgrupos')->updateOrInsert(
                ['codigo_subgrupo' => $subgrupo['codigo_subgrupo']],
                $subgrupo
            );
        }

        $this->command->info("✅ " . count($subgrupos) . " subgrupos de materiales creados/verificados");
    }
}

