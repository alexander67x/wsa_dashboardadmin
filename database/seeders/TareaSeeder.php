<?php

namespace Database\Seeders;

use App\Models\Proyecto;
use App\Models\Hito;
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

            $numTareas = rand(6, 12);
            
            for ($i = 1; $i <= $numTareas; $i++) {
                $hito = $hitos[($i - 1) % $hitos->count()];
                $fechaInicio = Carbon::parse($hito->fecha_hito)->addDays(rand(0, 2));
                $fechaFin = (clone $fechaInicio)->addDays(rand(1, 5));
                $estado = $estados[array_rand($estados)];
                $prioridad = $prioridades[array_rand($prioridades)];
                $responsable = $empleados->random();
                
                Tarea::create([
                    'cod_proy' => $proyecto->cod_proy,
                    'id_hito' => $hito->id_hito,
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
}
