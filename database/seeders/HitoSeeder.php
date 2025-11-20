<?php

namespace Database\Seeders;

use App\Models\Empleado;
use App\Models\Hito;
use App\Models\Proyecto;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HitoSeeder extends Seeder
{
    /**
     * Crea hitos semanales por cada proyecto.
     */
    public function run(): void
    {
        $proyectos = Proyecto::all();

        if ($proyectos->isEmpty()) {
            $this->call([ProyectoSeeder::class]);
            $proyectos = Proyecto::all();
        }

        $empleadoFallback = Empleado::first() ?? Empleado::factory()->create();
        $tipos = ['intermedio', 'entrega', 'revision'];

        foreach ($proyectos as $proyecto) {
            $inicioRef = Carbon::now()->startOfWeek();

            for ($week = 0; $week < 4; $week++) {
                $inicio = (clone $inicioRef)->addWeeks($week);
                $fin = (clone $inicio)->addDays(6);

                $titulo = "Semana " . ($week + 1) . " - {$proyecto->nombre_ubicacion}";

                Hito::updateOrCreate(
                    [
                        'cod_proy' => $proyecto->cod_proy,
                        'titulo' => $titulo,
                        'fecha_hito' => $inicio->toDateString(),
                    ],
                    [
                        'fecha_final_hito' => $fin->toDateString(),
                        'descripcion' => "Actividades planificadas para la semana " . ($week + 1),
                        'tipo' => $tipos[array_rand($tipos)],
                        'es_critico' => $week === 3,
                        'estado' => 'pendiente',
                        'creado_por' => $proyecto->responsable_proyecto ?? $empleadoFallback->cod_empleado,
                    ]
                );
            }
        }

        $this->command->info('Hitos semanales generados para los proyectos.');
    }
}
