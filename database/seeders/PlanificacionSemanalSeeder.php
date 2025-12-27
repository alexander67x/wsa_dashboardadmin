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

        $hydroExpected = [4, 11, 19, 30, 45, 58, 70, 82, 91, 100];
        $hydroReal = [6, 15, 27, 33, 40, 55, 69, 78, 85, 96];

        foreach ($proyectos as $proyecto) {
            $isHydro = $proyecto->cod_proy === 'SEG-HIDRO-004';
            $inicio = $isHydro
                ? Carbon::now()->startOfYear()->addWeeks(8)->startOfWeek()
                : Carbon::now()->subWeeks(4)->startOfWeek();
            $weeks = $isHydro ? count($hydroExpected) : 8;

            for ($i = 0; $i < $weeks; $i++) {
                $semanaCarbon = (clone $inicio)->addWeeks($i);
                $semana = (int) $semanaCarbon->isoWeek();
                $year = (int) $semanaCarbon->isoWeekYear();
                $avanceEsperado = $isHydro ? $hydroExpected[$i] : min(100, 10 * ($i + 1));
                $avanceReal = $isHydro ? $hydroReal[$i] : rand(55, 100);

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
                        'avance_esperado_porcentaje' => $avanceEsperado,
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
                        'porcentaje_cumplimiento' => $avanceReal,
                        'avance_real_porcentaje' => $avanceReal,
                        'causas_atraso' => null,
                        'estado' => $this->resolveEstado($avanceEsperado, $avanceReal),
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

    private function resolveEstado(float $expected, float $real): string
    {
        if ($real >= $expected + 5) {
            return 'adelantado';
        }

        if ($real + 5 < $expected) {
            return 'atrasado';
        }

        return 'en_tiempo';
    }
}
