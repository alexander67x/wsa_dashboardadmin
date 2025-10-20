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
        Empleado::create([
            'cod_empleado' => 1,
            'nombre_completo' => 'Ana María López',
            'cargo' => 'Gerente de Proyectos',
            'departamento' => 'Gerencia',
            'email' => 'ana.lopez@empresa.com',
            'telefono' => '+591-2-1111111',
            'fecha_ingreso' => '2020-01-15',
            'activo' => true,
        ]);

        Empleado::create([
            'cod_empleado' => 2,
            'nombre_completo' => 'Roberto Silva',
            'cargo' => 'Supervisor de Obra',
            'departamento' => 'Construcción',
            'email' => 'roberto.silva@empresa.com',
            'telefono' => '+591-2-2222222',
            'fecha_ingreso' => '2019-03-20',
            'activo' => true,
        ]);

        Empleado::create([
            'cod_empleado' => 3,
            'nombre_completo' => 'Carmen Vargas',
            'cargo' => 'Ingeniera Civil',
            'departamento' => 'Ingeniería',
            'email' => 'carmen.vargas@empresa.com',
            'telefono' => '+591-2-3333333',
            'fecha_ingreso' => '2021-06-10',
            'activo' => true,
        ]);

        Empleado::create([
            'cod_empleado' => 4,
            'nombre_completo' => 'Miguel Torres',
            'cargo' => 'Arquitecto',
            'departamento' => 'Diseño',
            'email' => 'miguel.torres@empresa.com',
            'telefono' => '+591-2-4444444',
            'fecha_ingreso' => '2020-09-05',
            'activo' => true,
        ]);
    }
}
