<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProyectoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegurar un cliente para los proyectos (si no existen clientes)
        $cliente = DB::table('clientes')->first();
        if (! $cliente) {
            $clienteId = DB::table('clientes')->insertGetId([
                'nombre_cliente' => 'Banco Universal S.A.',
                'industria' => 'Banca y finanzas',
                'contacto_principal' => 'Luis Herrera',
                'email' => 'luis.herrera@bancouniversal.com',
                'telefono' => '+591-2-1234567',
                'direccion' => 'Av. Mariscal Santa Cruz #100, La Paz',
                'activo' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } else {
            $clienteId = $cliente->cod_cliente;
        }

        // Asegurar responsables de proyecto y supervisores orientados a seguridad
        $responsable = DB::table('empleados')
            ->where('cargo', 'Gerente de Proyectos de Seguridad')
            ->first();

        if (! $responsable) {
            $responsableId = DB::table('empleados')->insertGetId([
                'nombre_completo' => 'Ana María López',
                'cargo' => 'Gerente de Proyectos de Seguridad',
                'departamento' => 'Gerencia de seguridad electrónica',
                'email' => 'ana.lopez@seguridadintegral.com',
                'telefono' => '+591-2-1111111',
                'fecha_ingreso' => Carbon::now()->subYears(4),
                'activo' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } else {
            $responsableId = $responsable->cod_empleado;
        }

        $supervisor = DB::table('empleados')
            ->where('cargo', 'Supervisor de Instalaciones')
            ->first();

        if (! $supervisor) {
            $supervisorId = DB::table('empleados')->insertGetId([
                'nombre_completo' => 'Roberto Silva',
                'cargo' => 'Supervisor de Instalaciones',
                'departamento' => 'Operaciones de campo',
                'email' => 'roberto.silva@seguridadintegral.com',
                'telefono' => '+591-2-2222222',
                'fecha_ingreso' => Carbon::now()->subYears(5),
                'activo' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } else {
            $supervisorId = $supervisor->cod_empleado;
        }

        // Proyectos de instalación de sistemas de seguridad
        $proyectos = [
            [
                'cod_proy' => 'PROY-001',
                'nombre_ubicacion' => 'CCTV Sucursal Central Banco Universal',
                'direccion' => 'Av. Mariscal Santa Cruz #100, La Paz',
                'ciudad' => 'La Paz',
                'pais' => 'Bolivia',
                'latitud' => -16.5000000,
                'longitud' => -68.1500000,
                'fecha_inicio' => Carbon::now()->subMonth(),
                'fecha_fin_estimada' => Carbon::now()->addMonths(2),
                'descripcion' => 'Instalación de sistema de videovigilancia IP de alta definición en todas las áreas críticas de la sucursal central.',
                'avance_financiero' => 85000.00,
                'gasto_real' => 65000.00,
                'rentabilidad' => 20,
            ],
            [
                'cod_proy' => 'PROY-002',
                'nombre_ubicacion' => 'Control de acceso en centro de datos',
                'direccion' => 'Zona Sur, Parque Empresarial, La Paz',
                'ciudad' => 'La Paz',
                'pais' => 'Bolivia',
                'latitud' => -16.5100000,
                'longitud' => -68.1300000,
                'fecha_inicio' => Carbon::now()->subWeeks(3),
                'fecha_fin_estimada' => Carbon::now()->addMonth(),
                'descripcion' => 'Implementación de lectores biométricos, cerraduras electrónicas y registro de visitas en el centro de datos principal.',
                'avance_financiero' => 60000.00,
                'gasto_real' => 40000.00,
                'rentabilidad' => 18,
            ],
            [
                'cod_proy' => 'PROY-003',
                'nombre_ubicacion' => 'Sistema integral de alarma y sensores en clínica',
                'direccion' => 'Av. Blanco Galindo #789, Cochabamba',
                'ciudad' => 'Cochabamba',
                'pais' => 'Bolivia',
                'latitud' => -17.3800000,
                'longitud' => -66.1500000,
                'fecha_inicio' => Carbon::now()->subDays(10),
                'fecha_fin_estimada' => Carbon::now()->addMonths(3),
                'descripcion' => 'Diseño e instalación de sensores de humo, movimiento y rotura de vidrios, integrados a central de alarmas y notificación.',
                'avance_financiero' => 95000.00,
                'gasto_real' => 50000.00,
                'rentabilidad' => 22,
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

        $this->command->info('✔ Proyectos de seguridad activos creados/actualizados: ' . count($proyectos));
    }
}

