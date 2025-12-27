<?php

namespace Database\Seeders;

use App\Models\Proyecto;
use App\Models\Hito;
use App\Models\Tarea;
use App\Models\Empleado;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TareaSeeder extends Seeder
{
    /**
     * Ejecuta los seeders de la base de datos.
     */
    public function run(): void
    {
        
        $proyectos = Proyecto::all();
        
        if ($proyectos->isEmpty()) {            $this->call([ProyectoSeeder::class]);
            $proyectos = Proyecto::all();
        }

        $empleados = Empleado::all();
        
        if ($empleados->isEmpty()) {
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

        $estados = ['pendiente', 'en_proceso', 'en_revision', 'finalizada', 'cancelada'];
        $prioridades = ['baja', 'media', 'alta'];
        
        foreach ($proyectos as $proyecto) {
            $hitos = Hito::where('cod_proy', $proyecto->cod_proy)
                ->orderBy('fecha_hito')
                ->get();

            if ($hitos->isEmpty()) {
                $this->command->warn("El proyecto {$proyecto->cod_proy} no tiene hitos. Ejecuta HitoSeeder antes de TareaSeeder.");
                continue;
            }

            if ($proyecto->cod_proy === 'SEG-HIDRO-004') {
                $this->seedHydroProjectTasks($proyecto, $empleados, $hitos);
                continue;
            }

            $numTareas = rand(6, 12);
            
            for ($i = 1; $i <= $numTareas; $i++) {
                $hito = $hitos[($i - 1) % $hitos->count()];
                $fechaInicio = Carbon::parse($hito->fecha_hito)->addDays(rand(0, 2));
                $fechaFin = (clone $fechaInicio)->addDays(rand(1, 5));
                $estado = $estados[array_rand($estados)];
                $prioridad = $prioridades[array_rand($prioridades)];
                $cantidadResponsables = (int) min(3, max(1, $empleados->count()));
                $seleccion = $empleados->random(rand(1, $cantidadResponsables));
                $responsablesSeleccionados = $seleccion instanceof \Illuminate\Support\Collection
                    ? $seleccion
                    : collect([$seleccion]);
                
                $tarea = Tarea::create([
                    'cod_proy' => $proyecto->cod_proy,
                    'id_hito' => $hito->id_hito,
                    'titulo' => 'Tarea ' . $i . ' - ' . $proyecto->nombre_ubicacion,
                    'descripcion' => 'Descripción detallada de la tarea ' . $i . ' para el proyecto ' . $proyecto->nombre_ubicacion,
                    'estado' => $estado,
                    'prioridad' => $prioridad,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'duracion_dias' => $fechaInicio->diffInDays($fechaFin),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                $tarea->responsables()->sync(
                    $responsablesSeleccionados
                        ->pluck('cod_empleado')
                        ->unique()
                        ->toArray()
                );
            }
        }

        // Asignar hitos a tareas existentes sin relación
        foreach ($proyectos as $proyecto) {
            $hitos = Hito::where('cod_proy', $proyecto->cod_proy)->get();

            if ($hitos->isEmpty()) {
                continue;
            }

            $tareasSinHito = Tarea::where('cod_proy', $proyecto->cod_proy)
                ->whereNull('id_hito')
                ->get();

            foreach ($tareasSinHito as $index => $tarea) {
                $hito = $hitos[$index % $hitos->count()];
                $tarea->update(['id_hito' => $hito->id_hito]);
            }
        }
        
        $this->command->info('Se han creado tareas de ejemplo para los proyectos existentes.');
    }

    private function seedHydroProjectTasks(Proyecto $proyecto, $empleados, $hitos): void
    {
        $schedule = [
            ['week_offset' => 8, 'planned' => 6, 'completed' => 4],
            ['week_offset' => 9, 'planned' => 6, 'completed' => 5],
            ['week_offset' => 10, 'planned' => 6, 'completed' => 3],
            ['week_offset' => 11, 'planned' => 6, 'completed' => 6],
            ['week_offset' => 12, 'planned' => 7, 'completed' => 4],
            ['week_offset' => 13, 'planned' => 7, 'completed' => 5],
            ['week_offset' => 14, 'planned' => 7, 'completed' => 6],
            ['week_offset' => 15, 'planned' => 7, 'completed' => 6],
            ['week_offset' => 16, 'planned' => 7, 'completed' => 5],
            ['week_offset' => 17, 'planned' => 7, 'completed' => 7],
        ];

        $prioridades = ['baja', 'media', 'alta'];
        $estados = ['en_proceso', 'finalizada'];
        $inicio = Carbon::now()->startOfYear();
        $taskIndex = 1;

        foreach ($schedule as $weekIndex => $slot) {
            $semanaInicio = $inicio->copy()->addWeeks($slot['week_offset'])->startOfWeek();
            $hito = $hitos[$weekIndex % $hitos->count()];

            for ($i = 0; $i < $slot['planned']; $i++) {
                $startDate = $semanaInicio->copy()->addDays($i % 5);
                $isCompleted = $i < $slot['completed'];
                $endDate = $isCompleted ? $startDate->copy()->addDays(2) : null;
                $estado = $isCompleted ? 'finalizada' : 'en_proceso';
                $prioridad = $prioridades[$taskIndex % count($prioridades)];

                $responsables = $empleados
                    ->random(min(2, $empleados->count()))
                    ->pluck('cod_empleado')
                    ->unique()
                    ->toArray();

                $tarea = Tarea::create([
                    'cod_proy' => $proyecto->cod_proy,
                    'id_hito' => $hito->id_hito,
                    'titulo' => 'Fase ' . ($weekIndex + 1) . ' - Actividad ' . $taskIndex,
                    'descripcion' => 'Actividad planificada para el complejo hidroeléctrico.',
                    'estado' => $estado,
                    'prioridad' => $prioridad,
                    'fecha_inicio' => $startDate,
                    'fecha_fin' => $endDate,
                    'duracion_dias' => $endDate ? $startDate->diffInDays($endDate) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $tarea->responsables()->sync($responsables);
                $taskIndex++;
            }
        }
    }
}
