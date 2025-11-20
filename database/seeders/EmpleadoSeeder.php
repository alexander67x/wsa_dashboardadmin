<?php

namespace Database\Seeders;

use App\Models\AsignacionProyecto;
use App\Models\Empleado;
use App\Models\Proyecto;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $puestos = [
            [
                'email' => 'gerencia@gmail.com',
                'nombre' => 'Gerente General',
                'cargo' => 'Gerente General',
                'departamento' => 'Dirección',
                'telefono' => '+591-2-1111111',
                'fecha' => '2019-01-01',
                'role' => 'gerencia',
                'user' => [
                    'email' => 'gerencia@gmail.com',
                    'name' => 'Gerente General',
                    'password' => '123',
                ],
            ],
            [
                'email' => 'adquisiciones@empresa.com',
                'nombre' => 'Mariana Huanca',
                'cargo' => 'Jefa de Adquisiciones',
                'departamento' => 'Logística',
                'telefono' => '+591-2-2222222',
                'fecha' => '2020-06-15',
                'role' => 'adquisiciones',
                'user' => [
                    'email' => 'adquisiciones@empresa.com',
                    'name' => 'Jefa Adquisiciones',
                    'password' => 'adquisiciones123',
                ],
            ],
            [
                'email' => 'proyectos@empresa.com',
                'nombre' => 'Luis Paredes',
                'cargo' => 'Responsable de Proyectos',
                'departamento' => 'Proyectos',
                'telefono' => '+591-2-3333333',
                'fecha' => '2021-04-10',
                'role' => 'responsable_proyecto',
                'user' => [
                    'email' => 'proyectos@empresa.com',
                    'name' => 'Responsable Proyecto',
                    'password' => 'proyectos123',
                ],
            ],
            [
                'email' => 'supervisor@empresa.com',
                'nombre' => 'Carlos Salinas',
                'cargo' => 'Supervisor de Obra',
                'departamento' => 'Operaciones',
                'telefono' => '+591-2-4444444',
                'fecha' => '2021-09-05',
                'role' => 'supervisor',
                'user' => [
                    'email' => 'supervisor@empresa.com',
                    'name' => 'Supervisor Obra',
                    'password' => 'supervisor123',
                ],
            ],
            [
                'email' => 'personal@empresa.com',
                'nombre' => 'Roberto Choque',
                'cargo' => 'Técnico de Campo',
                'departamento' => 'Operaciones',
                'telefono' => '+591-2-5555555',
                'fecha' => '2022-01-10',
                'role' => 'personal_obra',
                'user' => [
                    'email' => 'personal@empresa.com',
                    'name' => 'Personal de Obra',
                    'password' => 'personal123',
                ],
            ],
        ];

        $roles = Role::pluck('id_role', 'slug')->toArray();

        $assignmentMap = [
            'gerencia@gmail.com' => ['PROY-001'],
            'adquisiciones@empresa.com' => ['PROY-001'],
            'proyectos@empresa.com' => ['PROY-001', 'PROY-002'],
            'supervisor@empresa.com' => ['PROY-001'],
            'personal@empresa.com' => ['PROY-001'],
        ];

        foreach ($puestos as $puesto) {
            $user = null;
            if (! empty($puesto['user'])) {
                $user = User::firstOrCreate(
                    ['email' => $puesto['user']['email']],
                    [
                        'name' => $puesto['user']['name'],
                        'password' => Hash::make($puesto['user']['password']),
                    ],
                );
            }

            $empleado = Empleado::firstOrCreate(
                ['email' => $puesto['email']],
                [
                    'nombre_completo' => $puesto['nombre'],
                    'cargo' => $puesto['cargo'],
                    'departamento' => $puesto['departamento'],
                    'telefono' => $puesto['telefono'],
                    'fecha_ingreso' => $puesto['fecha'],
                    'activo' => true,
                ],
            );

            if ($user) {
                $empleado->user()->associate($user);
            }

            if (isset($roles[$puesto['role']])) {
                $empleado->id_role = $roles[$puesto['role']];
            }

            $empleado->save();

            foreach ($assignmentMap[$puesto['email']] ?? [] as $codProy) {
                $proyecto = Proyecto::where('cod_proy', $codProy)->first();
                if (! $proyecto) {
                    continue;
                }

                AsignacionProyecto::firstOrCreate(
                    [
                        'cod_proy' => $codProy,
                        'cod_empleado' => $empleado->cod_empleado,
                    ],
                    [
                        'fecha_inicio_asignacion' => Carbon::now()->subDays(10),
                        'rol_en_proyecto' => $puesto['role'],
                        'estado' => 'activo',
                    ]
                );
            }

            $this->command->info("✔ {$empleado->nombre_completo} asignado como {$puesto['role']} (Usuario: {$puesto['user']['email']})");
        }
    }
}
