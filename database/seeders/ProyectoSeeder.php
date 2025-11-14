<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProyectoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Verificar si existen clientes; si no, crear uno
        $cliente = DB::table('clientes')->first();
        if (!$cliente) {
            $clienteId = DB::table('clientes')->insertGetId([
                'nombre_cliente' => 'Cliente Ejemplo S.A.',
                'industria' => 'Construcción',
                'contacto_principal' => 'Juan Pérez',
                'email' => 'juan.perez@ejemplo.com',
                'telefono' => '123456789',
                'direccion' => 'Av. Principal 123, La Paz',
                'activo' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } else {
            $clienteId = $cliente->cod_cliente;
        }

        // Verificar si existen empleados; si no, crear dos (uno para responsable, otro para supervisor)
        $responsable = DB::table('empleados')->where('cargo', 'Gerente de Proyecto')->first();
        if (!$responsable) {
            $responsableId = DB::table('empleados')->insertGetId([
                'nombre_completo' => 'Ana Gómez',
                'cargo' => 'Gerente de Proyecto',
                'departamento' => 'Gestión de Proyectos',
                'email' => 'ana.gomez@empresa.com',
                'telefono' => '987654321',
                'fecha_ingreso' => Carbon::now()->subYear(),
                'activo' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } else {
            $responsableId = $responsable->cod_empleado;
        }

        $supervisor = DB::table('empleados')->where('cargo', 'Supervisor de Obra')->first();
        if (!$supervisor) {
            $supervisorId = DB::table('empleados')->insertGetId([
                'nombre_completo' => 'Carlos López',
                'cargo' => 'Supervisor de Obra',
                'departamento' => 'Operaciones',
                'email' => 'carlos.lopez@empresa.com',
                'telefono' => '912345678',
                'fecha_ingreso' => Carbon::now()->subYear(),
                'activo' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } else {
            $supervisorId = $supervisor->cod_empleado;
        }

        // Crear proyectos activos con códigos fijos para asegurar almacenes asociados
        $proyectos = [
            [
                'cod_proy' => 'PROY-001',
                'nombre_ubicacion' => 'Construcción Edificio Central',
                'direccion' => 'Calle 10, Zona Sur, La Paz',
                'ciudad' => 'La Paz',
                'pais' => 'Bolivia',
                'latitud' => -16.5000000,
                'longitud' => -68.1500000,
                'fecha_inicio' => Carbon::now()->subMonth(),
                'fecha_fin_estimada' => Carbon::now()->addMonths(6),
                'descripcion' => 'Construcción de un edificio de oficinas de 10 pisos.',
                'avance_financiero' => 250000.00,
                'gasto_real' => 200000.00,
                'rentabilidad' => 15,
            ],
            [
                'cod_proy' => 'PROY-002',
                'nombre_ubicacion' => 'Remodelación Centro Comercial',
                'direccion' => 'Av. 6 de Agosto, Zona Central, La Paz',
                'ciudad' => 'La Paz',
                'pais' => 'Bolivia',
                'latitud' => -16.5100000,
                'longitud' => -68.1300000,
                'fecha_inicio' => Carbon::now()->subWeeks(2),
                'fecha_fin_estimada' => Carbon::now()->addMonths(4),
                'descripcion' => 'Remodelación completa del centro comercial.',
                'avance_financiero' => 180000.00,
                'gasto_real' => 150000.00,
                'rentabilidad' => 12,
            ],
            [
                'cod_proy' => 'PROY-003',
                'nombre_ubicacion' => 'Construcción Residencial Zona Norte',
                'direccion' => 'Calle 15, Zona Norte, La Paz',
                'ciudad' => 'La Paz',
                'pais' => 'Bolivia',
                'latitud' => -16.4800000,
                'longitud' => -68.1200000,
                'fecha_inicio' => Carbon::now()->subDays(10),
                'fecha_fin_estimada' => Carbon::now()->addMonths(8),
                'descripcion' => 'Construcción de complejo residencial de 5 edificios.',
                'avance_financiero' => 320000.00,
                'gasto_real' => 280000.00,
                'rentabilidad' => 18,
            ],
        ];

        foreach ($proyectos as $proyectoData) {
            DB::table('proyectos')->updateOrInsert(
                ['cod_proy' => $proyectoData['cod_proy']],
                array_merge($proyectoData, [
                    'cod_cliente' => $clienteId,
                    'tipo_ubicacion' => 'obra',
                    'fecha_fin_real' => null,
                    'estado' => 'activo',
                    'responsable_proyecto' => $responsableId,
                    'supervisor_obra' => $supervisorId,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ])
            );
        }

        $this->command->info('✅ Proyectos activos creados/actualizados: ' . count($proyectos));
    }
}