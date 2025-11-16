<?php

namespace Database\Seeders;

use App\Models\Incidencia;
use App\Models\IncidenciaHistorial;
use App\Models\IncidenciaEvidencia;
use App\Models\Archivo;
use App\Models\Proyecto;
use App\Models\Tarea;
use App\Models\Empleado;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class IncidenciaSeeder extends Seeder
{
    /**
     * Ejecuta el seeder de incidencias.
     */
    public function run(): void
    {
        // Verificar que existan proyectos
        $proyectos = Proyecto::all();
        
        if ($proyectos->isEmpty()) {
            $this->command->warn('No hay proyectos disponibles. Ejecutando ProyectoSeeder...');
            $this->call([ProyectoSeeder::class]);
            $proyectos = Proyecto::all();
        }

        // Verificar que existan tareas
        $tareas = Tarea::all();
        
        if ($tareas->isEmpty()) {
            $this->command->warn('No hay tareas disponibles. Ejecutando TareaSeeder...');
            $this->call([TareaSeeder::class]);
            $tareas = Tarea::all();
        }

        // Verificar que existan empleados
        $empleados = Empleado::where('activo', true)->get();
        
        if ($empleados->isEmpty()) {
            $this->command->warn('No hay empleados disponibles. Ejecutando EmpleadoSeeder...');
            $this->call([EmpleadoSeeder::class]);
            $empleados = Empleado::where('activo', true)->get();
        }

        if ($empleados->isEmpty()) {
            $this->command->error('No se pueden crear incidencias sin empleados.');
            return;
        }

        $tipos = ['falla_equipos', 'accidente', 'retraso_material', 'problema_calidad', 'otro'];
        $severidades = ['critica', 'alta', 'media', 'baja'];
        $estados = ['abierta', 'en_proceso', 'resuelta', 'verificacion', 'cerrada', 'reabierta'];

        $titulos = [
            'Falla en equipo de soldadura',
            'Accidente menor en obra',
            'Retraso en entrega de materiales de construcción',
            'Problema de calidad en acabados',
            'Fuga de agua en instalación de plomería',
            'Falla eléctrica en panel principal',
            'Accidente con maquinaria pesada',
            'Retraso en suministro de cemento',
            'Defecto en calidad de pintura',
            'Problema estructural en cimentación',
            'Falla en sistema de seguridad',
            'Accidente con herramienta manual',
        ];

        $descripciones = [
            'Se detectó una falla en el equipo de soldadura que está retrasando el trabajo de estructura metálica. El equipo no genera suficiente calor.',
            'Ocurrió un accidente menor donde un trabajador se lastimó la mano. Se aplicaron primeros auxilios y se reportó al supervisor.',
            'El proveedor de materiales no ha entregado el pedido a tiempo, lo que está afectando el cronograma de la obra.',
            'Se encontraron imperfecciones en los acabados de las paredes que no cumplen con los estándares de calidad establecidos.',
            'Se detectó una fuga de agua en las tuberías del segundo piso que requiere atención inmediata.',
            'El panel eléctrico principal presenta fallas intermitentes que están afectando el suministro de energía.',
            'Accidente reportado con maquinaria pesada. No hubo heridos graves pero requiere investigación.',
            'El proveedor de cemento no cumplió con la fecha de entrega comprometida, retrasando la cimentación.',
            'La pintura aplicada presenta defectos de adherencia y requiere corrección.',
            'Se detectó un problema estructural en una sección de la cimentación que requiere evaluación.',
            'El sistema de seguridad presenta fallas en las cámaras de vigilancia de la obra.',
            'Accidente menor reportado con herramienta manual. El trabajador recibió atención médica.',
        ];

        $soluciones = [
            'Se reemplazó el equipo de soldadura defectuoso por uno nuevo. El trabajo continúa con normalidad.',
            'Se implementaron medidas preventivas adicionales y se reforzó el uso de equipos de protección personal.',
            'Se contactó con un proveedor alternativo y se recibió el material con un retraso de 2 días.',
            'Se corrigieron los acabados aplicando técnicas adecuadas y se verificó la calidad final.',
            'Se reparó la fuga reemplazando la sección dañada de la tubería. Sistema funcionando correctamente.',
            'Se reemplazaron los componentes defectuosos del panel eléctrico. Sistema operativo nuevamente.',
            'Se realizó inspección completa de la maquinaria y se establecieron protocolos de seguridad adicionales.',
            'Se coordinó con el proveedor y se recibió el cemento. Se ajustó el cronograma para compensar el retraso.',
            'Se removió la pintura defectuosa y se aplicó una nueva capa siguiendo las especificaciones técnicas.',
            'Se realizó evaluación estructural y se aplicaron refuerzos necesarios en la cimentación.',
            'Se actualizó el sistema de seguridad y se reemplazaron las cámaras defectuosas.',
            'Se implementaron controles adicionales para el uso seguro de herramientas manuales.',
        ];

        $comentariosHistorial = [
            'Incidencia reportada y registrada en el sistema.',
            'Incidencia asignada al responsable para su resolución.',
            'Se inició el trabajo de resolución de la incidencia.',
            'Incidencia resuelta satisfactoriamente.',
            'Enviada a verificación para validar la solución implementada.',
            'Incidencia verificada y cerrada.',
            'Incidencia reabierta debido a problemas detectados en la verificación.',
            'Solución implementada y documentada.',
            'Se requiere revisión adicional.',
            'Problema resuelto completamente.',
        ];

        $this->command->info('🚨 Creando incidencias de ejemplo...');
        $this->command->newLine();

        $contador = 0;

        // Crear incidencias en diferentes estados para probar el flujo completo
        
        // 1. Incidencias ABIERTAS
        for ($i = 0; $i < 3; $i++) {
            $proyecto = $proyectos->random();
            $tarea = $tareas->where('cod_proy', $proyecto->cod_proy)->first() ?? $tareas->random();
            $reportadoPor = $empleados->random();
            
            $fechaReportado = Carbon::now()->subDays(rand(1, 7));
            
            $incidencia = Incidencia::create([
                'cod_proy' => $proyecto->cod_proy,
                'id_tarea' => $tarea->id_tarea,
                'titulo' => $titulos[array_rand($titulos)],
                'descripcion' => $descripciones[array_rand($descripciones)],
                'tipo_incidencia' => $tipos[array_rand($tipos)],
                'severidad' => $severidades[array_rand($severidades)],
                'estado' => 'abierta',
                'latitud' => -16.5 + (rand(-100, 100) / 1000), // Coordenadas aproximadas de Bolivia
                'longitud' => -68.15 + (rand(-100, 100) / 1000),
                'reportado_por' => $reportadoPor->cod_empleado,
                'asignado_a' => null,
                'fecha_reportado' => $fechaReportado,
                'fecha_resolucion' => null,
                'solucion_implementada' => null,
                'created_at' => $fechaReportado,
                'updated_at' => $fechaReportado,
            ]);

            // Crear historial inicial
            IncidenciaHistorial::create([
                'id_incidencia' => $incidencia->id_incidencia,
                'estado_anterior' => null,
                'estado_nuevo' => 'abierta',
                'comentario' => $comentariosHistorial[0],
                'accion_tomada' => 'Creación de incidencia',
                'usuario_cambio' => $reportadoPor->cod_empleado,
                'fecha_cambio' => $fechaReportado,
            ]);

            $contador++;
        }

        // 2. Incidencias EN PROCESO
        for ($i = 0; $i < 2; $i++) {
            $proyecto = $proyectos->random();
            $tarea = $tareas->where('cod_proy', $proyecto->cod_proy)->first() ?? $tareas->random();
            $reportadoPor = $empleados->random();
            $asignadoA = $empleados->where('cod_empleado', '!=', $reportadoPor->cod_empleado)->random() ?? $empleados->random();
            
            $fechaReportado = Carbon::now()->subDays(rand(3, 10));
            $fechaEnProceso = (clone $fechaReportado)->addDays(rand(1, 2));
            
            $incidencia = Incidencia::create([
                'cod_proy' => $proyecto->cod_proy,
                'id_tarea' => $tarea->id_tarea,
                'titulo' => $titulos[array_rand($titulos)],
                'descripcion' => $descripciones[array_rand($descripciones)],
                'tipo_incidencia' => $tipos[array_rand($tipos)],
                'severidad' => $severidades[array_rand($severidades)],
                'estado' => 'en_proceso',
                'latitud' => -16.5 + (rand(-100, 100) / 1000),
                'longitud' => -68.15 + (rand(-100, 100) / 1000),
                'reportado_por' => $reportadoPor->cod_empleado,
                'asignado_a' => $asignadoA->cod_empleado,
                'fecha_reportado' => $fechaReportado,
                'fecha_resolucion' => null,
                'solucion_implementada' => null,
                'created_at' => $fechaReportado,
                'updated_at' => $fechaEnProceso,
            ]);

            // Historial: Abierta -> En Proceso
            IncidenciaHistorial::create([
                'id_incidencia' => $incidencia->id_incidencia,
                'estado_anterior' => null,
                'estado_nuevo' => 'abierta',
                'comentario' => $comentariosHistorial[0],
                'accion_tomada' => 'Creación de incidencia',
                'usuario_cambio' => $reportadoPor->cod_empleado,
                'fecha_cambio' => $fechaReportado,
            ]);

            IncidenciaHistorial::create([
                'id_incidencia' => $incidencia->id_incidencia,
                'estado_anterior' => 'abierta',
                'estado_nuevo' => 'en_proceso',
                'comentario' => $comentariosHistorial[1],
                'accion_tomada' => 'Incidencia puesta en proceso',
                'usuario_cambio' => $asignadoA->cod_empleado,
                'fecha_cambio' => $fechaEnProceso,
            ]);

            $contador++;
        }

        // 3. Incidencias RESUELTAS
        for ($i = 0; $i < 2; $i++) {
            $proyecto = $proyectos->random();
            $tarea = $tareas->where('cod_proy', $proyecto->cod_proy)->first() ?? $tareas->random();
            $reportadoPor = $empleados->random();
            $asignadoA = $empleados->where('cod_empleado', '!=', $reportadoPor->cod_empleado)->random() ?? $empleados->random();
            
            $fechaReportado = Carbon::now()->subDays(rand(5, 15));
            $fechaEnProceso = (clone $fechaReportado)->addDays(rand(1, 2));
            $fechaResuelta = (clone $fechaEnProceso)->addDays(rand(2, 5));
            
            $incidencia = Incidencia::create([
                'cod_proy' => $proyecto->cod_proy,
                'id_tarea' => $tarea->id_tarea,
                'titulo' => $titulos[array_rand($titulos)],
                'descripcion' => $descripciones[array_rand($descripciones)],
                'tipo_incidencia' => $tipos[array_rand($tipos)],
                'severidad' => $severidades[array_rand($severidades)],
                'estado' => 'resuelta',
                'latitud' => -16.5 + (rand(-100, 100) / 1000),
                'longitud' => -68.15 + (rand(-100, 100) / 1000),
                'reportado_por' => $reportadoPor->cod_empleado,
                'asignado_a' => $asignadoA->cod_empleado,
                'fecha_reportado' => $fechaReportado,
                'fecha_resolucion' => $fechaResuelta,
                'solucion_implementada' => $soluciones[array_rand($soluciones)],
                'created_at' => $fechaReportado,
                'updated_at' => $fechaResuelta,
            ]);

            // Historial completo: Abierta -> En Proceso -> Resuelta
            IncidenciaHistorial::create([
                'id_incidencia' => $incidencia->id_incidencia,
                'estado_anterior' => null,
                'estado_nuevo' => 'abierta',
                'comentario' => $comentariosHistorial[0],
                'accion_tomada' => 'Creación de incidencia',
                'usuario_cambio' => $reportadoPor->cod_empleado,
                'fecha_cambio' => $fechaReportado,
            ]);

            IncidenciaHistorial::create([
                'id_incidencia' => $incidencia->id_incidencia,
                'estado_anterior' => 'abierta',
                'estado_nuevo' => 'en_proceso',
                'comentario' => $comentariosHistorial[1],
                'accion_tomada' => 'Incidencia puesta en proceso',
                'usuario_cambio' => $asignadoA->cod_empleado,
                'fecha_cambio' => $fechaEnProceso,
            ]);

            IncidenciaHistorial::create([
                'id_incidencia' => $incidencia->id_incidencia,
                'estado_anterior' => 'en_proceso',
                'estado_nuevo' => 'resuelta',
                'comentario' => $comentariosHistorial[3],
                'accion_tomada' => 'Incidencia marcada como resuelta',
                'usuario_cambio' => $asignadoA->cod_empleado,
                'fecha_cambio' => $fechaResuelta,
            ]);

            $contador++;
        }

        // 4. Incidencias en VERIFICACIÓN
        for ($i = 0; $i < 2; $i++) {
            $proyecto = $proyectos->random();
            $tarea = $tareas->where('cod_proy', $proyecto->cod_proy)->first() ?? $tareas->random();
            $reportadoPor = $empleados->random();
            $asignadoA = $empleados->where('cod_empleado', '!=', $reportadoPor->cod_empleado)->random() ?? $empleados->random();
            
            $fechaReportado = Carbon::now()->subDays(rand(7, 20));
            $fechaEnProceso = (clone $fechaReportado)->addDays(rand(1, 3));
            $fechaResuelta = (clone $fechaEnProceso)->addDays(rand(2, 5));
            $fechaVerificacion = (clone $fechaResuelta)->addDays(1);
            
            $incidencia = Incidencia::create([
                'cod_proy' => $proyecto->cod_proy,
                'id_tarea' => $tarea->id_tarea,
                'titulo' => $titulos[array_rand($titulos)],
                'descripcion' => $descripciones[array_rand($descripciones)],
                'tipo_incidencia' => $tipos[array_rand($tipos)],
                'severidad' => $severidades[array_rand($severidades)],
                'estado' => 'verificacion',
                'latitud' => -16.5 + (rand(-100, 100) / 1000),
                'longitud' => -68.15 + (rand(-100, 100) / 1000),
                'reportado_por' => $reportadoPor->cod_empleado,
                'asignado_a' => $asignadoA->cod_empleado,
                'fecha_reportado' => $fechaReportado,
                'fecha_resolucion' => $fechaResuelta,
                'solucion_implementada' => $soluciones[array_rand($soluciones)],
                'created_at' => $fechaReportado,
                'updated_at' => $fechaVerificacion,
            ]);

            // Historial completo hasta verificación
            IncidenciaHistorial::create([
                'id_incidencia' => $incidencia->id_incidencia,
                'estado_anterior' => null,
                'estado_nuevo' => 'abierta',
                'comentario' => $comentariosHistorial[0],
                'accion_tomada' => 'Creación de incidencia',
                'usuario_cambio' => $reportadoPor->cod_empleado,
                'fecha_cambio' => $fechaReportado,
            ]);

            IncidenciaHistorial::create([
                'id_incidencia' => $incidencia->id_incidencia,
                'estado_anterior' => 'abierta',
                'estado_nuevo' => 'en_proceso',
                'comentario' => $comentariosHistorial[1],
                'accion_tomada' => 'Incidencia puesta en proceso',
                'usuario_cambio' => $asignadoA->cod_empleado,
                'fecha_cambio' => $fechaEnProceso,
            ]);

            IncidenciaHistorial::create([
                'id_incidencia' => $incidencia->id_incidencia,
                'estado_anterior' => 'en_proceso',
                'estado_nuevo' => 'resuelta',
                'comentario' => $comentariosHistorial[3],
                'accion_tomada' => 'Incidencia marcada como resuelta',
                'usuario_cambio' => $asignadoA->cod_empleado,
                'fecha_cambio' => $fechaResuelta,
            ]);

            IncidenciaHistorial::create([
                'id_incidencia' => $incidencia->id_incidencia,
                'estado_anterior' => 'resuelta',
                'estado_nuevo' => 'verificacion',
                'comentario' => $comentariosHistorial[4],
                'accion_tomada' => 'Incidencia enviada a verificación',
                'usuario_cambio' => $asignadoA->cod_empleado,
                'fecha_cambio' => $fechaVerificacion,
            ]);

            $contador++;
        }

        // 5. Incidencias CERRADAS
        for ($i = 0; $i < 3; $i++) {
            $proyecto = $proyectos->random();
            $tarea = $tareas->where('cod_proy', $proyecto->cod_proy)->first() ?? $tareas->random();
            $reportadoPor = $empleados->random();
            $asignadoA = $empleados->where('cod_empleado', '!=', $reportadoPor->cod_empleado)->random() ?? $empleados->random();
            $verificador = $empleados->where('cod_empleado', '!=', $asignadoA->cod_empleado)->random() ?? $empleados->random();
            
            $fechaReportado = Carbon::now()->subDays(rand(10, 30));
            $fechaEnProceso = (clone $fechaReportado)->addDays(rand(1, 3));
            $fechaResuelta = (clone $fechaEnProceso)->addDays(rand(2, 6));
            $fechaVerificacion = (clone $fechaResuelta)->addDays(1);
            $fechaCerrada = (clone $fechaVerificacion)->addDays(rand(1, 3));
            
            $incidencia = Incidencia::create([
                'cod_proy' => $proyecto->cod_proy,
                'id_tarea' => $tarea->id_tarea,
                'titulo' => $titulos[array_rand($titulos)],
                'descripcion' => $descripciones[array_rand($descripciones)],
                'tipo_incidencia' => $tipos[array_rand($tipos)],
                'severidad' => $severidades[array_rand($severidades)],
                'estado' => 'cerrada',
                'latitud' => -16.5 + (rand(-100, 100) / 1000),
                'longitud' => -68.15 + (rand(-100, 100) / 1000),
                'reportado_por' => $reportadoPor->cod_empleado,
                'asignado_a' => $asignadoA->cod_empleado,
                'fecha_reportado' => $fechaReportado,
                'fecha_resolucion' => $fechaResuelta,
                'solucion_implementada' => $soluciones[array_rand($soluciones)],
                'created_at' => $fechaReportado,
                'updated_at' => $fechaCerrada,
            ]);

            // Historial completo hasta cerrada
            IncidenciaHistorial::create([
                'id_incidencia' => $incidencia->id_incidencia,
                'estado_anterior' => null,
                'estado_nuevo' => 'abierta',
                'comentario' => $comentariosHistorial[0],
                'accion_tomada' => 'Creación de incidencia',
                'usuario_cambio' => $reportadoPor->cod_empleado,
                'fecha_cambio' => $fechaReportado,
            ]);

            IncidenciaHistorial::create([
                'id_incidencia' => $incidencia->id_incidencia,
                'estado_anterior' => 'abierta',
                'estado_nuevo' => 'en_proceso',
                'comentario' => $comentariosHistorial[1],
                'accion_tomada' => 'Incidencia puesta en proceso',
                'usuario_cambio' => $asignadoA->cod_empleado,
                'fecha_cambio' => $fechaEnProceso,
            ]);

            IncidenciaHistorial::create([
                'id_incidencia' => $incidencia->id_incidencia,
                'estado_anterior' => 'en_proceso',
                'estado_nuevo' => 'resuelta',
                'comentario' => $comentariosHistorial[3],
                'accion_tomada' => 'Incidencia marcada como resuelta',
                'usuario_cambio' => $asignadoA->cod_empleado,
                'fecha_cambio' => $fechaResuelta,
            ]);

            IncidenciaHistorial::create([
                'id_incidencia' => $incidencia->id_incidencia,
                'estado_anterior' => 'resuelta',
                'estado_nuevo' => 'verificacion',
                'comentario' => $comentariosHistorial[4],
                'accion_tomada' => 'Incidencia enviada a verificación',
                'usuario_cambio' => $asignadoA->cod_empleado,
                'fecha_cambio' => $fechaVerificacion,
            ]);

            IncidenciaHistorial::create([
                'id_incidencia' => $incidencia->id_incidencia,
                'estado_anterior' => 'verificacion',
                'estado_nuevo' => 'cerrada',
                'comentario' => $comentariosHistorial[5],
                'accion_tomada' => 'Incidencia cerrada',
                'usuario_cambio' => $verificador->cod_empleado,
                'fecha_cambio' => $fechaCerrada,
            ]);

            $contador++;
        }

        // 6. Incidencia REABIERTA (para probar el flujo de reapertura)
        $proyecto = $proyectos->random();
        $tarea = $tareas->where('cod_proy', $proyecto->cod_proy)->first() ?? $tareas->random();
        $reportadoPor = $empleados->random();
        $asignadoA = $empleados->where('cod_empleado', '!=', $reportadoPor->cod_empleado)->random() ?? $empleados->random();
        $verificador = $empleados->where('cod_empleado', '!=', $asignadoA->cod_empleado)->random() ?? $empleados->random();
        
        $fechaReportado = Carbon::now()->subDays(rand(15, 35));
        $fechaEnProceso = (clone $fechaReportado)->addDays(rand(1, 3));
        $fechaResuelta = (clone $fechaEnProceso)->addDays(rand(2, 6));
        $fechaVerificacion = (clone $fechaResuelta)->addDays(1);
        $fechaReabierta = (clone $fechaVerificacion)->addDays(rand(1, 2));
        
        $incidencia = Incidencia::create([
            'cod_proy' => $proyecto->cod_proy,
            'id_tarea' => $tarea->id_tarea,
            'titulo' => $titulos[array_rand($titulos)],
            'descripcion' => $descripciones[array_rand($descripciones)],
            'tipo_incidencia' => $tipos[array_rand($tipos)],
            'severidad' => 'alta', // Reabiertas suelen ser de alta severidad
            'estado' => 'reabierta',
            'latitud' => -16.5 + (rand(-100, 100) / 1000),
            'longitud' => -68.15 + (rand(-100, 100) / 1000),
            'reportado_por' => $reportadoPor->cod_empleado,
            'asignado_a' => $asignadoA->cod_empleado,
            'fecha_reportado' => $fechaReportado,
            'fecha_resolucion' => $fechaResuelta,
            'solucion_implementada' => $soluciones[array_rand($soluciones)],
            'created_at' => $fechaReportado,
            'updated_at' => $fechaReabierta,
        ]);

        // Historial completo incluyendo reapertura
        IncidenciaHistorial::create([
            'id_incidencia' => $incidencia->id_incidencia,
            'estado_anterior' => null,
            'estado_nuevo' => 'abierta',
            'comentario' => $comentariosHistorial[0],
            'accion_tomada' => 'Creación de incidencia',
            'usuario_cambio' => $reportadoPor->cod_empleado,
            'fecha_cambio' => $fechaReportado,
        ]);

        IncidenciaHistorial::create([
            'id_incidencia' => $incidencia->id_incidencia,
            'estado_anterior' => 'abierta',
            'estado_nuevo' => 'en_proceso',
            'comentario' => $comentariosHistorial[1],
            'accion_tomada' => 'Incidencia puesta en proceso',
            'usuario_cambio' => $asignadoA->cod_empleado,
            'fecha_cambio' => $fechaEnProceso,
        ]);

        IncidenciaHistorial::create([
            'id_incidencia' => $incidencia->id_incidencia,
            'estado_anterior' => 'en_proceso',
            'estado_nuevo' => 'resuelta',
            'comentario' => $comentariosHistorial[3],
            'accion_tomada' => 'Incidencia marcada como resuelta',
            'usuario_cambio' => $asignadoA->cod_empleado,
            'fecha_cambio' => $fechaResuelta,
        ]);

        IncidenciaHistorial::create([
            'id_incidencia' => $incidencia->id_incidencia,
            'estado_anterior' => 'resuelta',
            'estado_nuevo' => 'verificacion',
            'comentario' => $comentariosHistorial[4],
            'accion_tomada' => 'Incidencia enviada a verificación',
            'usuario_cambio' => $asignadoA->cod_empleado,
            'fecha_cambio' => $fechaVerificacion,
        ]);

        IncidenciaHistorial::create([
            'id_incidencia' => $incidencia->id_incidencia,
            'estado_anterior' => 'verificacion',
            'estado_nuevo' => 'reabierta',
            'comentario' => 'Se detectaron problemas en la verificación. La solución no fue completamente efectiva.',
            'accion_tomada' => 'Incidencia reabierta',
            'usuario_cambio' => $verificador->cod_empleado,
            'fecha_cambio' => $fechaReabierta,
        ]);

        $contador++;

        $this->command->info("✅ Se han creado {$contador} incidencias de ejemplo.");
        $this->command->newLine();
        
        // Mostrar resumen por estado
        $resumen = Incidencia::selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->get();
        
        if ($resumen->isNotEmpty()) {
            $this->command->info('📊 Resumen por estado:');
            foreach ($resumen as $item) {
                $estadoLabel = match($item->estado) {
                    'abierta' => 'Abierta',
                    'en_proceso' => 'En Proceso',
                    'resuelta' => 'Resuelta',
                    'verificacion' => 'Verificación',
                    'cerrada' => 'Cerrada',
                    'reabierta' => 'Reabierta',
                    default => $item->estado,
                };
                $this->command->line("   • {$estadoLabel}: {$item->total}");
            }
        }

        $this->command->newLine();
        $totalHistorial = IncidenciaHistorial::count();
        $this->command->info("📝 Se han creado {$totalHistorial} registros de historial.");
    }
}




