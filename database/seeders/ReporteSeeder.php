<?php

namespace Database\Seeders;

use App\Models\ReporteAvanceTarea;
use App\Models\Tarea;
use App\Models\Empleado;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReporteSeeder extends Seeder
{
    /**
     * Ejecuta el seeder de reportes.
     */
    public function run(): void
    {
        // Verificar que existan tareas
        $tareas = Tarea::all();
        
        if ($tareas->isEmpty()) {
            $this->command->warn('No hay tareas disponibles. Ejecutando TareaSeeder...');
            $this->call([TareaSeeder::class]);
            $tareas = Tarea::all();
        }

        // Verificar que existan empleados
        $empleados = Empleado::all();
        
        if ($empleados->isEmpty()) {
            $this->command->warn('No hay empleados disponibles. Ejecutando EmpleadoSeeder...');
            $this->call([EmpleadoSeeder::class]);
            $empleados = Empleado::all();
        }

        $estados = ['borrador', 'enviado', 'aprobado', 'rechazado'];
        $titulos = [
            'Avance en instalación eléctrica',
            'Reporte de progreso en cimentación',
            'Actualización de obra en segundo piso',
            'Avance en instalación de plomería',
            'Reporte de terminaciones',
            'Progreso en estructura metálica',
            'Avance en instalación de ventanas',
            'Reporte de pintura y acabados',
            'Progreso en instalación de pisos',
            'Avance en instalación de techos',
        ];

        $dificultadesLista = [
            'Retraso en entrega de materiales',
            'Condiciones climáticas adversas',
            'Falta de personal especializado',
            'Problemas con permisos municipales',
            'Cambios en el diseño solicitados por el cliente',
            null, // Algunos reportes sin dificultades
            null,
        ];

        $materialesLista = [
            'Cemento, arena, grava, varillas de acero',
            'Tuberías PVC, conexiones, válvulas',
            'Cables eléctricos, interruptores, tomas',
            'Pintura, brochas, rodillos, masilla',
            'Ladrillos, cemento, arena',
            'Vidrios, marcos de aluminio, selladores',
            null, // Algunos reportes sin materiales
            null,
        ];

        $observaciones = [
            'Excelente trabajo, continuar así',
            'Revisar calidad en los acabados',
            'Aprobado con observaciones menores',
            'Necesita corrección en la alineación',
            'Falta documentación fotográfica',
            'Rechazado por incumplimiento de especificaciones',
            'Revisar materiales utilizados',
            null,
        ];

        $this->command->info('📋 Creando reportes de ejemplo...');
        $this->command->newLine();

        $contador = 0;

        // Crear reportes para cada tarea
        foreach ($tareas as $tarea) {
            // Crear entre 0 y 3 reportes por tarea
            $numReportes = rand(0, 3);
            
            for ($i = 0; $i < $numReportes; $i++) {
                $estado = $estados[array_rand($estados)];
                $fechaReporte = Carbon::now()->subDays(rand(0, 60));
                
                // Seleccionar un empleado aleatorio para registrar
                $registradoPor = $empleados->random();
                
                // Para reportes aprobados o rechazados, necesitamos un aprobador
                $aprobadoPor = null;
                $fechaAprobacion = null;
                $observacionesSupervisor = null;
                
                if (in_array($estado, ['aprobado', 'rechazado'])) {
                    // Seleccionar un supervisor diferente al que registró
                    $supervisores = $empleados->where('cod_empleado', '!=', $registradoPor->cod_empleado);
                    if ($supervisores->isNotEmpty()) {
                        $aprobadoPor = $supervisores->random();
                    } else {
                        $aprobadoPor = $empleados->random();
                    }
                    
                    $fechaAprobacion = (clone $fechaReporte)->addDays(rand(1, 5));
                    $observacionesSupervisor = $observaciones[array_rand($observaciones)];
                }

                $titulo = $titulos[array_rand($titulos)] . ' - ' . $tarea->titulo;
                $descripcion = $this->generarDescripcion($tarea, $i + 1);
                $dificultades = $dificultadesLista[array_rand($dificultadesLista)];
                $materialesUtilizados = $materialesLista[array_rand($materialesLista)];

                ReporteAvanceTarea::create([
                    'id_tarea' => $tarea->id_tarea,
                    'cod_proy' => $tarea->cod_proy,
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'fecha_reporte' => $fechaReporte,
                    'dificultades_encontradas' => $dificultades,
                    'materiales_utilizados' => $materialesUtilizados,
                    'registrado_por' => $registradoPor->cod_empleado,
                    'estado' => $estado,
                    'observaciones_supervisor' => $observacionesSupervisor,
                    'fecha_aprobacion' => $fechaAprobacion,
                    'aprobado_por' => $aprobadoPor?->cod_empleado,
                    'created_at' => $fechaReporte,
                    'updated_at' => $fechaAprobacion ?? $fechaReporte,
                ]);

                $contador++;
            }
        }

        $this->command->info("✅ Se han creado {$contador} reportes de ejemplo.");
        $this->command->newLine();
        
        // Mostrar resumen por estado
        $resumen = ReporteAvanceTarea::selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->get();
        
        if ($resumen->isNotEmpty()) {
            $this->command->info('📊 Resumen por estado:');
            foreach ($resumen as $item) {
                $estadoLabel = match($item->estado) {
                    'borrador' => 'Borrador',
                    'enviado' => 'Pendiente',
                    'aprobado' => 'Aprobado',
                    'rechazado' => 'Rechazado',
                    default => $item->estado,
                };
                $this->command->line("   • {$estadoLabel}: {$item->total}");
            }
        }
    }

    /**
     * Genera una descripción de ejemplo para el reporte.
     */
    private function generarDescripcion($tarea, $numero): string
    {
        $descripciones = [
            "Se ha completado el {$numero}% del avance en la tarea. El trabajo se está realizando según lo planificado.",
            "Reporte de avance: Se han ejecutado las actividades programadas para esta semana. Todo marcha según cronograma.",
            "Actualización del estado de la tarea. Se han encontrado algunos retrasos menores pero manejables.",
            "Progreso satisfactorio en la ejecución. Se han cumplido los objetivos planteados para este período.",
            "Reporte detallado del avance. Se requiere atención en algunos aspectos técnicos.",
            "Estado actual de la obra: Se han completado las fases iniciales y se avanza hacia las siguientes etapas.",
        ];

        $base = $descripciones[array_rand($descripciones)];
        return $base . " Tarea: {$tarea->titulo}";
    }
}

