<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\Empleado;
use App\Models\Proyecto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlmacenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si existen empleados; si no, usar el EmpleadoSeeder
        $empleados = Empleado::all();
        if ($empleados->isEmpty()) {
            $this->call([EmpleadoSeeder::class]);
            $empleados = Empleado::all();
        }

        // Verificar si existen proyectos; si no, usar el ProyectoSeeder
        $proyectos = Proyecto::all();
        if ($proyectos->isEmpty()) {
            $this->call([ProyectoSeeder::class]);
            $proyectos = Proyecto::all();
        }

        // Obtener empleados para usar como responsables
        $responsableCentral = $empleados->where('cargo', 'Gerente de Proyectos')->first() 
            ?? $empleados->where('cargo', 'Gerente de Proyecto')->first()
            ?? $empleados->first();

        $supervisor = $empleados->where('cargo', 'Supervisor de Obra')->first() 
            ?? $empleados->skip(1)->first()
            ?? $empleados->first();

        // 1. Crear Almacén Central
        $almacenCentral = Almacen::firstOrCreate(
            ['codigo_almacen' => 'ALM-CENTRAL-001'],
            [
                'nombre' => 'Almacén Central Principal',
                'direccion' => 'Av. Industrial 500, Zona Industrial, La Paz',
                'ciudad' => 'La Paz',
                'pais' => 'Bolivia',
                'latitud' => -16.5000000,
                'longitud' => -68.1500000,
                'tipo_ubicacion' => 'almacen',
                'responsable' => $responsableCentral->cod_empleado,
                'tipo' => 'central',
                'id_almacen_padre' => null,
                'cod_proy' => null,
                'activo' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        $this->command->info("Almacén Central creado: {$almacenCentral->nombre}");

        // 2. Crear Almacenes de Proyecto (dependen del almacén central)
        $almacenesProyecto = [];
        foreach ($proyectos as $index => $proyecto) {
            $responsableProyecto = $empleados->skip($index % $empleados->count())->first();
            
            $almacenProyecto = Almacen::firstOrCreate(
                ['codigo_almacen' => 'ALM-PROY-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT)],
                [
                    'nombre' => 'Almacén - ' . $proyecto->nombre_ubicacion,
                    'direccion' => $proyecto->direccion ?? 'Dirección del proyecto',
                    'ciudad' => $proyecto->ciudad ?? 'La Paz',
                    'pais' => $proyecto->pais ?? 'Bolivia',
                    'latitud' => $proyecto->latitud ?? -16.5000000,
                    'longitud' => $proyecto->longitud ?? -68.1500000,
                    'tipo_ubicacion' => 'almacen',
                    'responsable' => $responsableProyecto->cod_empleado,
                    'tipo' => 'proyecto',
                    'id_almacen_padre' => $almacenCentral->id_almacen,
                    'cod_proy' => $proyecto->cod_proy,
                    'activo' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );

            $almacenesProyecto[] = $almacenProyecto;
            $this->command->info("Almacén de Proyecto creado: {$almacenProyecto->nombre} para proyecto {$proyecto->cod_proy}");
        }

        // 3. Crear algunos Almacenes Temporales (opcional)
        $almacenesTemporales = [
            [
                'codigo_almacen' => 'ALM-TEMP-001',
                'nombre' => 'Almacén Temporal - Obra Norte',
                'direccion' => 'Calle Temporal 123, Zona Norte',
                'ciudad' => 'La Paz',
                'pais' => 'Bolivia',
                'latitud' => -16.4800000,
                'longitud' => -68.1200000,
                'responsable' => $supervisor->cod_empleado,
            ],
            [
                'codigo_almacen' => 'ALM-TEMP-002',
                'nombre' => 'Almacén Temporal - Obra Sur',
                'direccion' => 'Av. Temporal 456, Zona Sur',
                'ciudad' => 'La Paz',
                'pais' => 'Bolivia',
                'latitud' => -16.5200000,
                'longitud' => -68.1800000,
                'responsable' => $empleados->skip(2)->first()->cod_empleado ?? $supervisor->cod_empleado,
            ],
        ];

        foreach ($almacenesTemporales as $tempData) {
            $almacenTemporal = Almacen::firstOrCreate(
                ['codigo_almacen' => $tempData['codigo_almacen']],
                array_merge($tempData, [
                    'tipo_ubicacion' => 'temporal',
                    'tipo' => 'temporal',
                    'id_almacen_padre' => $almacenCentral->id_almacen,
                    'cod_proy' => null,
                    'activo' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ])
            );

            $this->command->info("Almacén Temporal creado: {$almacenTemporal->nombre}");
        }

        $totalAlmacenes = Almacen::count();
        $this->command->info("✅ Seeder completado. Total de almacenes: {$totalAlmacenes}");
        $this->command->info("   - 1 Almacén Central");
        $this->command->info("   - " . count($almacenesProyecto) . " Almacenes de Proyecto");
        $this->command->info("   - " . count($almacenesTemporales) . " Almacenes Temporales");
    }
}

