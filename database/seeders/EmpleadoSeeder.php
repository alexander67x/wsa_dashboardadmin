<?php

namespace Database\Seeders;

use App\Models\Empleado;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $empleado1 = Empleado::firstOrCreate(
            ['email' => 'ana.lopez@empresa.com'],
            [
                'nombre_completo' => 'Ana María López',
                'cargo' => 'Gerente de Proyectos',
                'departamento' => 'Gerencia',
                'telefono' => '+591-2-1111111',
                'fecha_ingreso' => '2020-01-15',
                'activo' => true,
            ]
        );

        $empleado2 = Empleado::firstOrCreate(
            ['email' => 'roberto.silva@empresa.com'],
            [
                'nombre_completo' => 'Roberto Silva',
                'cargo' => 'Supervisor de Obra',
                'departamento' => 'Construcción',
                'telefono' => '+591-2-2222222',
                'fecha_ingreso' => '2019-03-20',
                'activo' => true,
            ]
        );

        $empleado3 = Empleado::firstOrCreate(
            ['email' => 'carmen.vargas@empresa.com'],
            [
                'nombre_completo' => 'Carmen Vargas',
                'cargo' => 'Ingeniera Civil',
                'departamento' => 'Ingeniería',
                'telefono' => '+591-2-3333333',
                'fecha_ingreso' => '2021-06-10',
                'activo' => true,
            ]
        );

        $empleado4 = Empleado::firstOrCreate(
            ['email' => 'miguel.torres@empresa.com'],
            [
                'nombre_completo' => 'Miguel Torres',
                'cargo' => 'Arquitecto',
                'departamento' => 'Diseño',
                'telefono' => '+591-2-4444444',
                'fecha_ingreso' => '2020-09-05',
                'activo' => true,
            ]
        );

        $this->command->info("✅ Empleados creados/verificados:");
        $this->command->info("   - {$empleado1->nombre_completo} ({$empleado1->cargo})");
        $this->command->info("   - {$empleado2->nombre_completo} ({$empleado2->cargo})");
        $this->command->info("   - {$empleado3->nombre_completo} ({$empleado3->cargo})");
        $this->command->info("   - {$empleado4->nombre_completo} ({$empleado4->cargo})");
    }
}
