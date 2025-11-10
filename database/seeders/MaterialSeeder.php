<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\Material;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si existen subgrupos; si no, crearlos
        $subgrupos = DB::table('material_subgrupos')->get();
        if ($subgrupos->isEmpty()) {
            $this->call([MaterialSubgrupoSeeder::class]);
            $subgrupos = DB::table('material_subgrupos')->get();
        }

        // Verificar si existen almacenes; si no, crearlos
        $almacenes = Almacen::all();
        if ($almacenes->isEmpty()) {
            $this->call([AlmacenSeeder::class]);
            $almacenes = Almacen::all();
        }

        $almacenCentral = $almacenes->where('tipo', 'central')->first();
        $almacenesProyecto = $almacenes->where('tipo', 'proyecto')->take(2);

        // Obtener subgrupos de seguridad
        $subgrupoCamarasIP = $subgrupos->where('codigo_subgrupo', 'SUB-001')->first();
        $subgrupoCamarasAnalog = $subgrupos->where('codigo_subgrupo', 'SUB-002')->first();
        $subgrupoDVR = $subgrupos->where('codigo_subgrupo', 'SUB-003')->first();
        $subgrupoSensoresMov = $subgrupos->where('codigo_subgrupo', 'SUB-004')->first();
        $subgrupoSensoresHumo = $subgrupos->where('codigo_subgrupo', 'SUB-005')->first();
        $subgrupoBiometricos = $subgrupos->where('codigo_subgrupo', 'SUB-007')->first();
        $subgrupoTarjetas = $subgrupos->where('codigo_subgrupo', 'SUB-008')->first();
        $subgrupoUTP = $subgrupos->where('codigo_subgrupo', 'SUB-010')->first();
        $subgrupoCoaxial = $subgrupos->where('codigo_subgrupo', 'SUB-011')->first();
        $subgrupoSirenas = $subgrupos->where('codigo_subgrupo', 'SUB-012')->first();
        $subgrupoCercos = $subgrupos->where('codigo_subgrupo', 'SUB-013')->first();
        $subgrupoHerramientas = $subgrupos->where('codigo_subgrupo', 'SUB-014')->first();

        $materiales = [
            // Cámaras IP
            [
                'codigo_producto' => 'MAT-001',
                'nombre_producto' => 'Cámara IP 4MP Hikvision DS-2CD2347G1-LU',
                'id_subgrupo' => $subgrupoCamarasIP->id_subgrupo ?? $subgrupos->first()->id_subgrupo,
                'unidad_medida' => 'unidad',
                'costo_unitario_promedio_bs' => 1250.00,
                'stock_minimo' => 5,
                'stock_maximo' => 30,
                'criticidad' => 'critico',
                'activo' => true,
            ],
            [
                'codigo_producto' => 'MAT-002',
                'nombre_producto' => 'Cámara IP PTZ 5MP Dahua SD49225I-HC',
                'id_subgrupo' => $subgrupoCamarasIP->id_subgrupo ?? $subgrupos->first()->id_subgrupo,
                'unidad_medida' => 'unidad',
                'costo_unitario_promedio_bs' => 3200.00,
                'stock_minimo' => 2,
                'stock_maximo' => 10,
                'criticidad' => 'no_critico',
                'activo' => true,
            ],
            
            // Cámaras Análogas
            [
                'codigo_producto' => 'MAT-003',
                'nombre_producto' => 'Cámara Domo HDCVI 1080p Hikvision DS-2CE76D0T-ITMF',
                'id_subgrupo' => $subgrupoCamarasAnalog->id_subgrupo ?? $subgrupos->first()->id_subgrupo,
                'unidad_medida' => 'unidad',
                'costo_unitario_promedio_bs' => 420.00,
                'stock_minimo' => 10,
                'stock_maximo' => 50,
                'criticidad' => 'critico',
                'activo' => true,
            ],
            
            // DVR/NVR
            [
                'codigo_producto' => 'MAT-004',
                'nombre_producto' => 'DVR 8 Canales Hikvision DS-7208HGHI-K2',
                'id_subgrupo' => $subgrupoDVR->id_subgrupo ?? $subgrupos->first()->id_subgrupo,
                'unidad_medida' => 'unidad',
                'costo_unitario_promedio_bs' => 1800.00,
                'stock_minimo' => 2,
                'stock_maximo' => 10,
                'criticidad' => 'critico',
                'activo' => true,
            ],
            
            // Sensores de Movimiento
            [
                'codigo_producto' => 'MAT-005',
                'nombre_producto' => 'Sensor de Movimiento PIR Dahua DB-3A-12/24V',
                'id_subgrupo' => $subgrupoSensoresMov->id_subgrupo ?? $subgrupos->first()->id_subgrupo,
                'unidad_medida' => 'unidad',
                'costo_unitario_promedio_bs' => 85.50,
                'stock_minimo' => 15,
                'stock_maximo' => 50,
                'criticidad' => 'no_critico',
                'activo' => true,
            ],
            
            // Sensores de Humo
            [
                'codigo_producto' => 'MAT-006',
                'nombre_producto' => 'Detector de Humo Óptico DSC',
                'id_subgrupo' => $subgrupoSensoresHumo->id_subgrupo ?? $subgrupos->first()->id_subgrupo,
                'unidad_medida' => 'unidad',
                'costo_unitario_promedio_bs' => 150.00,
                'stock_minimo' => 10,
                'stock_maximo' => 30,
                'criticidad' => 'critico',
                'activo' => true,
            ],
            
            // Control de Acceso Biométrico
            [
                'codigo_producto' => 'MAT-007',
                'nombre_producto' => 'Lector Biométrico ZKTeco MB10',
                'id_subgrupo' => $subgrupoBiometricos->id_subgrupo ?? $subgrupos->first()->id_subgrupo,
                'unidad_medida' => 'unidad',
                'costo_unitario_promedio_bs' => 2800.00,
                'stock_minimo' => 2,
                'stock_maximo' => 10,
                'criticidad' => 'critico',
                'activo' => true,
            ],
            
            // Lectores de Tarjeta
            [
                'codigo_producto' => 'MAT-008',
                'nombre_producto' => 'Lector de Proximidad HID ProxPoint Plus',
                'id_subgrupo' => $subgrupoTarjetas->id_subgrupo ?? $subgrupos->first()->id_subgrupo,
                'unidad_medida' => 'unidad',
                'costo_unitario_promedio_bs' => 350.00,
                'stock_minimo' => 5,
                'stock_maximo' => 20,
                'criticidad' => 'no_critico',
                'activo' => true,
            ],
            
            // Cableado UTP
            [
                'codigo_producto' => 'MAT-009',
                'nombre_producto' => 'Cable UTP Cat6 CCA 305m',
                'id_subgrupo' => $subgrupoUTP->id_subgrupo ?? $subgrupos->first()->id_subgrupo,
                'unidad_medida' => 'rollo',
                'costo_unitario_promedio_bs' => 650.00,
                'stock_minimo' => 5,
                'stock_maximo' => 20,
                'criticidad' => 'critico',
                'activo' => true,
            ],
            
            // Cable Coaxial
            [
                'codigo_producto' => 'MAT-010',
                'nombre_producto' => 'Cable Coaxial RG59 150m',
                'id_subgrupo' => $subgrupoCoaxial->id_subgrupo ?? $subgrupos->first()->id_subgrupo,
                'unidad_medida' => 'rollo',
                'costo_unitario_promedio_bs' => 480.00,
                'stock_minimo' => 5,
                'stock_maximo' => 20,
                'criticidad' => 'critico',
                'activo' => true,
            ],
            
            // Sirenas
            [
                'codigo_producto' => 'MAT-011',
                'nombre_producto' => 'Sirena Externa 30W DSC',
                'id_subgrupo' => $subgrupoSirenas->id_subgrupo ?? $subgrupos->first()->id_subgrupo,
                'unidad_medida' => 'unidad',
                'costo_unitario_promedio_bs' => 320.00,
                'stock_minimo' => 3,
                'stock_maximo' => 15,
                'criticidad' => 'no_critico',
                'activo' => true,
            ],
            
            // Cercos Eléctricos
            [
                'codigo_producto' => 'MAT-012',
                'nombre_producto' => 'Cerca Eléctrica 1000m BFT',
                'id_subgrupo' => $subgrupoCercos->id_subgrupo ?? $subgrupos->first()->id_subgrupo,
                'unidad_medida' => 'kit',
                'costo_unitario_promedio_bs' => 12500.00,
                'stock_minimo' => 1,
                'stock_maximo' => 5,
                'criticidad' => 'no_critico',
                'activo' => true,
            ],
            
            // Herramientas
            [
                'codigo_producto' => 'MAT-013',
                'nombre_producto' => 'Kit Instalador CCTV Profesional',
                'id_subgrupo' => $subgrupoHerramientas->id_subgrupo ?? $subgrupos->first()->id_subgrupo,
                'unidad_medida' => 'kit',
                'costo_unitario_promedio_bs' => 850.00,
                'stock_minimo' => 2,
                'stock_maximo' => 10,
                'criticidad' => 'no_critico',
                'activo' => true
            ]
        ];

        $this->command->info("📦 Creando materiales...");
        $this->command->newLine();

        $materialesCreados = 0;
        foreach ($materiales as $materialData) {
            $material = Material::firstOrCreate(
                ['codigo_producto' => $materialData['codigo_producto']],
                array_merge($materialData, [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ])
            );

            // Asignar almacenes al material
            $almacenesParaMaterial = collect();
            
            // Todos los materiales van al almacén central
            if ($almacenCentral) {
                $almacenesParaMaterial->push($almacenCentral);
            }
            
            // Materiales críticos también van a almacenes de proyecto
            if ($material->criticidad === 'critico' && $almacenesProyecto->isNotEmpty()) {
                $almacenesParaMaterial = $almacenesParaMaterial->merge($almacenesProyecto);
            }

            // Crear registros en stock_almacen directamente
            if ($almacenesParaMaterial->isNotEmpty()) {
                foreach ($almacenesParaMaterial as $almacen) {
                    $stockInicial = $material->criticidad === 'critico' 
                        ? rand(50, 200) 
                        : rand(10, 50);
                    
                    DB::table('stock_almacen')->updateOrInsert(
                        [
                            'id_almacen' => $almacen->id_almacen,
                            'id_material' => $material->id_material,
                        ],
                        [
                            'cantidad_disponible' => $stockInicial,
                            'cantidad_reservada' => 0,
                            'cantidad_minima_alerta' => $material->stock_minimo,
                            'ubicacion_fisica' => 'Estante ' . rand(1, 10) . ', Pasillo ' . rand(1, 5),
                            'updated_at' => Carbon::now(),
                        ]
                    );
                }
            }

            $materialesCreados++;
            $this->command->info("✅ {$material->codigo_producto} - {$material->nombre_producto}");
        }

        $this->command->newLine();
        $totalMateriales = Material::count();
        $this->command->info("📊 Total de materiales en el sistema: {$totalMateriales}");
        $this->command->info("✅ {$materialesCreados} materiales creados/verificados con asignación a almacenes");
    }
}

