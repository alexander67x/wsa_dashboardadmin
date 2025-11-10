<?php

namespace Database\Seeders;

use App\Models\Proyecto;
use App\Models\Tarea;
use App\Models\Empleado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TareaSeeder extends Seeder
{
    /**
     * Ejecuta los seeders de la base de datos.
     */
    public function run(): void
    {
        // Obtener todos los proyectos existentes
        $proyectos = Proyecto::all();
        
        if ($proyectos->isEmpty()) {
            // Si no hay proyectos, crear uno primero
            $this->call([ProyectoSeeder::class]);
            $proyectos = Proyecto::all();
        }

        // Obtener empleados existentes o crear algunos si no hay
        $empleados = Empleado::all();
        
        if ($empleados->isEmpty()) {
            // Crear algunos empleados de ejemplo si no existen
            $empleado1 = Empleado::create([
                'nombre_completo' => 'Juan Pérez',
                'cargo' => 'Ingeniero Civil',
                'departamento' => 'Construcción',
                'email' => 'juan.perez@empresa.com',
                'telefono' => '123456789',
                'fecha_ingreso' => Carbon::now()->subYear(),
                'activo' => true,
            ]);

            $empleado2 = Empleado::create([
                'nombre_completo' => 'María López',
                'cargo' => 'Arquitecta',
                'departamento' => 'Diseño',
                'email' => 'maria.lopez@empresa.com',
                'telefono' => '987654321',
                'fecha_ingreso' => Carbon::now()->subYear(2),
                'activo' => true,
            ]);

            $empleados = collect([$empleado1, $empleado2]);
        }

        // Estados y prioridades posibles
        $estados = ['pendiente', 'en_proceso', 'en_revision', 'finalizada', 'cancelada'];
        $prioridades = ['baja', 'media', 'alta'];
        
        // Crear tareas para cada proyecto
        foreach ($proyectos as $proyecto) {
            // Crear entre 5 y 15 tareas por proyecto
            $numTareas = rand(5, 15);
            
            for ($i = 1; $i <= $numTareas; $i++) {
                $fechaInicio = Carbon::now()->subDays(rand(0, 30));
                $fechaFin = (clone $fechaInicio)->addDays(rand(1, 30));
                $estado = $estados[array_rand($estados)];
                $prioridad = $prioridades[array_rand($prioridades)];
                $responsable = $empleados->random();
                
                Tarea::create([
                    'cod_proy' => $proyecto->cod_proy,
                    'titulo' => 'Tarea ' . $i . ' - ' . $proyecto->nombre_ubicacion,
                    'descripcion' => 'Descripción detallada de la tarea ' . $i . ' para el proyecto ' . $proyecto->nombre_ubicacion,
                    'estado' => $estado,
                    'prioridad' => $prioridad,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'duracion_dias' => $fechaInicio->diffInDays($fechaFin),
                    'responsable_id' => $responsable->cod_empleado,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
        
        $this->command->info('Se han creado tareas de ejemplo para los proyectos existentes.');
    }
}
