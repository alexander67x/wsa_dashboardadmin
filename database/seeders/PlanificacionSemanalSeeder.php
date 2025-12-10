<?php

namespace Database\Seeders;

use App\Models\EjecucionSemanal;
use App\Models\Empleado;
use App\Models\PlanificacionSemanal;
use App\Models\Proyecto;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PlanificacionSemanalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $proyectos = Proyecto::all();

        if ($proyectos->isEmpty()) {
            $this->call([ProyectoSeeder::class]);
            $proyectos = Proyecto::all();
        }

        $empleado = Empleado::first();
        if (! $empleado) {
            $this->call([EmpleadoSeeder::class]);
            $empleado = Empleado::first();
        }

        if ($proyectos->isEmpty() || ! $empleado) {
            $this->command?->warn('No hay proyectos o empleados disponibles para generar planificaciones semanales.');
            return;
        }

        foreach ($proyectos as $proyecto) {
            $inicio = Carbon::now()->subWeeks(4)->startOfWeek();

            for ($i = 0; $i < 8; $i++) {
                $semanaCarbon = (clone $inicio)->addWeeks($i);
                $semana = (int) $semanaCarbon->isoWeek();
                $year = (int) $semanaCarbon->isoWeekYear();

                $plan = PlanificacionSemanal::updateOrCreate(
                    [
                        'cod_proy' => $proyecto->cod_proy,
                        'semana' => $semana,
                        'año' => $year,
                    ],
                    [
                        'actividades_planificadas' => "Actividades críticas para la semana {$semana}",
                        'hitos_esperados' => "Hito programado #{$i} del proyecto {$proyecto->cod_proy}",
                        'recursos_requeridos' => 'Equipo de instalación, herramientas especializadas y soporte técnico.',
                        'avance_esperado_porcentaje' => min(100, 10 * ($i + 1)),
                        'estado' => 'planificado',
                        'planificado_por' => $empleado->cod_empleado,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                EjecucionSemanal::updateOrCreate(
                    ['id_plan' => $plan->id_plan],
                    [
                        'actividades_ejecutadas' => 'Avance registrado desde campo y validado por el supervisor.',
                        'porcentaje_cumplimiento' => rand(65, 100),
                        'avance_real_porcentaje' => rand(55, 100),
                        'causas_atraso' => null,
                        'estado' => 'en_tiempo',
                        'reportado_por' => $empleado->cod_empleado,
                        'fecha_reporte' => $semanaCarbon->copy()->endOfWeek(),
                        'aprobado_por' => $empleado->cod_empleado,
                        'fecha_aprobacion' => $semanaCarbon->copy()->endOfWeek()->addDay(),
                    ]
                );
            }
        }

        $this->command?->info('Planificación y ejecución semanal de ejemplo creadas.');
    }
}
