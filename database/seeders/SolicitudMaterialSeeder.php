<?php

namespace Database\Seeders;

use App\Models\SolicitudMaterial;
use App\Models\SolicitudItem;
use App\Models\Proyecto;
use App\Models\Empleado;
use App\Models\Material;
use App\Models\Tarea;
use App\Models\Almacen;
use App\Models\StockAlmacen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SolicitudMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que existan los datos necesarios
        $proyectos = Proyecto::all();
        if ($proyectos->isEmpty()) {
            $this->command->warn('⚠️  No hay proyectos. Ejecuta ProyectoSeeder primero.');
            return;
        }

        $empleados = Empleado::where('activo', true)->get();
        if ($empleados->isEmpty()) {
            $this->command->warn('⚠️  No hay empleados. Ejecuta EmpleadoSeeder primero.');
            return;
        }

        $materiales = Material::where('activo', true)->get();
        if ($materiales->isEmpty()) {
            $this->command->warn('⚠️  No hay materiales. Ejecuta MaterialSeeder primero.');
            return;
        }

        $tareas = Tarea::all();
        
        // Empleados para diferentes roles
        $solicitantes = $empleados->random(min(3, $empleados->count()));
        $aprobadores = $empleados->filter(function ($empleado) {
            $cargo = strtolower($empleado->cargo ?? '');
            return str_contains($cargo, 'gerente') || 
                   str_contains($cargo, 'supervisor') || 
                   str_contains($cargo, 'jefe');
        })->take(2);
        
        if ($aprobadores->isEmpty()) {
            $aprobadores = $empleados->take(2);
        }

        $aprobador1 = $aprobadores->first();
        $aprobador2 = $aprobadores->skip(1)->first() ?? $aprobadores->first();

        // Obtener el último número de solicitud para continuar desde ahí
        $ultimaSolicitud = SolicitudMaterial::orderBy('id_solicitud', 'desc')->first();
        $contadorSolicitud = $ultimaSolicitud ? (int) substr($ultimaSolicitud->numero_solicitud, -4) + 1 : 1;

        // 1. Solicitud en BORRADOR
        $solicitud1 = $this->crearSolicitud([
            'numero_solicitud' => 'SOL-' . str_pad($contadorSolicitud++, 4, '0', STR_PAD_LEFT),
            'proyecto' => $proyectos->random(),
            'tarea' => $tareas->isNotEmpty() ? $tareas->random() : null,
            'solicitado_por' => $solicitantes->random(),
            'fecha_solicitud' => Carbon::now()->subDays(5),
            'fecha_requerida' => Carbon::now()->addDays(10),
            'estado' => 'borrador',
            'urgente' => false,
            'motivo' => 'Materiales para inicio de obra',
            'observaciones' => 'Solicitud en proceso de revisión',
        ], $materiales, [
            ['cantidad' => 50, 'unidad' => 'unidad'],
            ['cantidad' => 100, 'unidad' => 'metro'],
            ['cantidad' => 25, 'unidad' => 'kg'],
        ]);

        // 2. Solicitud PENDIENTE
        $solicitud2 = $this->crearSolicitud([
            'numero_solicitud' => 'SOL-' . str_pad($contadorSolicitud++, 4, '0', STR_PAD_LEFT),
            'proyecto' => $proyectos->random(),
            'tarea' => $tareas->isNotEmpty() ? $tareas->random() : null,
            'solicitado_por' => $solicitantes->random(),
            'fecha_solicitud' => Carbon::now()->subDays(3),
            'fecha_requerida' => Carbon::now()->addDays(7),
            'estado' => 'pendiente',
            'urgente' => true,
            'motivo' => 'Materiales urgentes para continuidad de obra',
            'observaciones' => 'Requiere aprobación inmediata',
        ], $materiales, [
            ['cantidad' => 200, 'unidad' => 'unidad'],
            ['cantidad' => 500, 'unidad' => 'metro'],
        ]);

        // 3. Solicitud APROBADA (sin entregas aún)
        $solicitud3 = $this->crearSolicitud([
            'numero_solicitud' => 'SOL-' . str_pad($contadorSolicitud++, 4, '0', STR_PAD_LEFT),
            'proyecto' => $proyectos->random(),
            'tarea' => $tareas->isNotEmpty() ? $tareas->random() : null,
            'solicitado_por' => $solicitantes->random(),
            'aprobada_por' => $aprobador1,
            'fecha_solicitud' => Carbon::now()->subDays(10),
            'fecha_requerida' => Carbon::now()->addDays(5),
            'fecha_aprobacion' => Carbon::now()->subDays(8),
            'estado' => 'aprobada',
            'urgente' => false,
            'motivo' => 'Materiales para fase de construcción',
            'observaciones' => 'Aprobada y lista para entrega',
        ], $materiales, [
            ['cantidad' => 150, 'unidad' => 'unidad'],
            ['cantidad' => 300, 'unidad' => 'metro'],
            ['cantidad' => 75, 'unidad' => 'kg'],
        ], true); // aprobada = true

        // 4. Solicitud ENVIADO (con entregas parciales)
        $solicitud4 = $this->crearSolicitud([
            'numero_solicitud' => 'SOL-' . str_pad($contadorSolicitud++, 4, '0', STR_PAD_LEFT),
            'proyecto' => $proyectos->random(),
            'tarea' => $tareas->isNotEmpty() ? $tareas->random() : null,
            'solicitado_por' => $solicitantes->random(),
            'aprobada_por' => $aprobador2,
            'fecha_solicitud' => Carbon::now()->subDays(15),
            'fecha_requerida' => Carbon::now()->subDays(5),
            'fecha_aprobacion' => Carbon::now()->subDays(12),
            'estado' => 'enviado',
            'urgente' => false,
            'motivo' => 'Materiales para instalación',
            'observaciones' => 'En proceso de entrega parcial',
        ], $materiales, [
            ['cantidad' => 80, 'unidad' => 'unidad', 'entregado' => 50], // 62.5% entregado
            ['cantidad' => 200, 'unidad' => 'metro', 'entregado' => 200], // 100% entregado
            ['cantidad' => 40, 'unidad' => 'kg', 'entregado' => 20], // 50% entregado
        ], true, true); // aprobada = true, con entregas = true

        // 5. Solicitud RECIBIDA (totalmente entregada)
        $solicitud5 = $this->crearSolicitud([
            'numero_solicitud' => 'SOL-' . str_pad($contadorSolicitud++, 4, '0', STR_PAD_LEFT),
            'proyecto' => $proyectos->random(),
            'tarea' => $tareas->isNotEmpty() ? $tareas->random() : null,
            'solicitado_por' => $solicitantes->random(),
            'aprobada_por' => $aprobador1,
            'fecha_solicitud' => Carbon::now()->subDays(20),
            'fecha_requerida' => Carbon::now()->subDays(10),
            'fecha_aprobacion' => Carbon::now()->subDays(18),
            'estado' => 'recibida',
            'urgente' => false,
            'motivo' => 'Materiales para finalización de obra',
            'observaciones' => 'Completamente recibida',
        ], $materiales, [
            ['cantidad' => 100, 'unidad' => 'unidad', 'entregado' => 100], // 100% entregado
            ['cantidad' => 250, 'unidad' => 'metro', 'entregado' => 250], // 100% entregado
        ], true, true); // aprobada = true, con entregas = true

        // 6. Solicitud RECHAZADA
        $solicitud6 = $this->crearSolicitud([
            'numero_solicitud' => 'SOL-' . str_pad($contadorSolicitud++, 4, '0', STR_PAD_LEFT),
            'proyecto' => $proyectos->random(),
            'tarea' => $tareas->isNotEmpty() ? $tareas->random() : null,
            'solicitado_por' => $solicitantes->random(),
            'aprobada_por' => $aprobador2,
            'fecha_solicitud' => Carbon::now()->subDays(7),
            'fecha_requerida' => Carbon::now()->addDays(3),
            'fecha_aprobacion' => Carbon::now()->subDays(5),
            'estado' => 'rechazada',
            'urgente' => false,
            'motivo' => 'Materiales no justificados',
            'observaciones' => 'Rechazada por falta de justificación adecuada',
        ], $materiales, [
            ['cantidad' => 500, 'unidad' => 'unidad'],
            ['cantidad' => 1000, 'unidad' => 'metro'],
        ], false); // aprobada = false

        // 7. Solicitud PENDIENTE que REQUIERE COMPRA (almacén padre con stock insuficiente)
        $solicitud7 = $this->crearSolicitudConCompra([
            'numero_solicitud' => 'SOL-' . str_pad($contadorSolicitud++, 4, '0', STR_PAD_LEFT),
            'proyecto' => $proyectos->random(),
            'tarea' => $tareas->isNotEmpty() ? $tareas->random() : null,
            'solicitado_por' => $solicitantes->random(),
            'fecha_solicitud' => Carbon::now()->subDays(2),
            'fecha_requerida' => Carbon::now()->addDays(5),
            'estado' => 'pendiente',
            'urgente' => true,
            'motivo' => 'Materiales urgentes - requiere compra',
            'observaciones' => 'El almacén padre no tiene stock suficiente.',
        ], $materiales, [
            ['cantidad' => 1000, 'unidad' => 'unidad'], // Cantidad alta que probablemente requiera compra
            ['cantidad' => 2000, 'unidad' => 'metro'],
            ['cantidad' => 500, 'unidad' => 'kg'],
        ]);

        // 8. Solicitud APROBADA que REQUIERE COMPRA (aprobada asumiendo compra)
        $solicitud8 = $this->crearSolicitudConCompra([
            'numero_solicitud' => 'SOL-' . str_pad($contadorSolicitud++, 4, '0', STR_PAD_LEFT),
            'proyecto' => $proyectos->random(),
            'tarea' => $tareas->isNotEmpty() ? $tareas->random() : null,
            'solicitado_por' => $solicitantes->random(),
            'aprobada_por' => $aprobador1,
            'fecha_solicitud' => Carbon::now()->subDays(8),
            'fecha_requerida' => Carbon::now()->addDays(3),
            'fecha_aprobacion' => Carbon::now()->subDays(6),
            'estado' => 'aprobada',
            'urgente' => false,
            'motivo' => 'Materiales para ampliación - requiere compra',
            'observaciones' => 'Aprobada asumiendo compra de materiales faltantes.',
        ], $materiales, [
            ['cantidad' => 800, 'unidad' => 'unidad'],
            ['cantidad' => 1500, 'unidad' => 'metro'],
        ], true); // aprobada = true

        // 9. Solicitud PENDIENTE con stock parcial (algunos items requieren compra, otros no)
        $solicitud9 = $this->crearSolicitud([
            'numero_solicitud' => 'SOL-' . str_pad($contadorSolicitud++, 4, '0', STR_PAD_LEFT),
            'proyecto' => $proyectos->random(),
            'tarea' => $tareas->isNotEmpty() ? $tareas->random() : null,
            'solicitado_por' => $solicitantes->random(),
            'fecha_solicitud' => Carbon::now()->subDays(1),
            'fecha_requerida' => Carbon::now()->addDays(7),
            'estado' => 'pendiente',
            'urgente' => false,
            'motivo' => 'Materiales mixtos - stock parcial disponible',
            'observaciones' => 'Algunos materiales están disponibles en almacén padre, otros requieren compra. Ver detalle de materiales faltantes abajo.',
        ], $materiales, [
            ['cantidad' => 30, 'unidad' => 'unidad'], // Cantidad pequeña que probablemente esté disponible
            ['cantidad' => 1500, 'unidad' => 'metro'], // Cantidad grande que probablemente requiera compra
            ['cantidad' => 40, 'unidad' => 'kg'], // Cantidad pequeña
        ]);

        $this->command->info('✅ Solicitudes de materiales creadas:');
        $this->command->info("   - {$solicitud1->numero_solicitud} (Borrador)");
        $this->command->info("   - {$solicitud2->numero_solicitud} (Pendiente)");
        $this->command->info("   - {$solicitud3->numero_solicitud} (Aprobada)");
        $this->command->info("   - {$solicitud4->numero_solicitud} (Enviado - Parcial)");
        $this->command->info("   - {$solicitud5->numero_solicitud} (Recibida - Completa)");
        $this->command->info("   - {$solicitud6->numero_solicitud} (Rechazada)");
        $this->command->info("   - {$solicitud7->numero_solicitud} (Pendiente - Requiere Compra)");
        $this->command->info("   - {$solicitud8->numero_solicitud} (Aprobada - Requiere Compra)");
        $this->command->info("   - {$solicitud9->numero_solicitud} (Pendiente - Stock Parcial)");
    }

    /**
     * Crea una solicitud con sus items
     */
    private function crearSolicitud(array $data, $materiales, array $itemsData, bool $aprobada = false, bool $conEntregas = false): SolicitudMaterial
    {
        $solicitud = SolicitudMaterial::create([
            'numero_solicitud' => $data['numero_solicitud'],
            'cod_proy' => $data['proyecto']->cod_proy,
            'id_tarea' => $data['tarea']?->id_tarea,
            'solicitado_por' => $data['solicitado_por']->cod_empleado,
            'cargo_solicitante' => $data['solicitado_por']->cargo,
            'centro_costos' => 'CC-' . $data['proyecto']->cod_proy,
            'fecha_solicitud' => $data['fecha_solicitud'],
            'fecha_requerida' => $data['fecha_requerida'],
            'estado' => $data['estado'],
            'requiere_aprobacion' => true,
            'aprobada_por' => isset($data['aprobada_por']) ? $data['aprobada_por']?->cod_empleado : null,
            'fecha_aprobacion' => $data['fecha_aprobacion'] ?? null,
            'motivo' => $data['motivo'],
            'observaciones' => $data['observaciones'],
            'urgente' => $data['urgente'] ?? false,
        ]);

        // Buscar almacén asociado al proyecto
        $almacen = Almacen::where('cod_proy', $data['proyecto']->cod_proy)
            ->where('activo', true)
            ->first();

        // Obtener almacén padre si existe
        $almacenPadre = null;
        if ($almacen && $almacen->id_almacen_padre) {
            $almacenPadre = Almacen::where('id_almacen', $almacen->id_almacen_padre)
                ->where('activo', true)
                ->first();
        }

        // Crear items de la solicitud
        $materialesSeleccionados = $materiales->random(min(count($itemsData), $materiales->count()))->values();
        
        foreach ($itemsData as $index => $itemData) {
            if ($index >= $materialesSeleccionados->count()) {
                break;
            }

            $material = $materialesSeleccionados->get($index);
            $cantidadSolicitada = $itemData['cantidad'];
            $cantidadAprobada = $aprobada ? $cantidadSolicitada : null;
            $cantidadEntregada = $conEntregas && isset($itemData['entregado']) ? $itemData['entregado'] : 0;

            // Consultar stock en almacén padre si existe
            $cantidadDisponiblePadre = 0;
            $cantidadFaltante = 0;
            $requiereCompra = false;
            
            if ($almacenPadre) {
                $stockPadre = StockAlmacen::where('id_almacen', $almacenPadre->id_almacen)
                    ->where('id_material', $material->id_material)
                    ->first();
                
                if ($stockPadre) {
                    // Calcular cantidad disponible (disponible - reservada)
                    $cantidadDisponiblePadre = max(0, $stockPadre->cantidad_disponible - $stockPadre->cantidad_reservada);
                }
                
                // Calcular cantidad faltante
                if ($cantidadDisponiblePadre < $cantidadSolicitada) {
                    $cantidadFaltante = $cantidadSolicitada - $cantidadDisponiblePadre;
                    $requiereCompra = true;
                }
            } else {
                // Si no hay almacén padre, se asume que requiere compra completa
                $cantidadFaltante = $cantidadSolicitada;
                $requiereCompra = true;
            }

            SolicitudItem::create([
                'id_solicitud' => $solicitud->id_solicitud,
                'id_material' => $material->id_material,
                'id_lote' => null, // Los lotes se asignan cuando llegan
                'cantidad_solicitada' => $cantidadSolicitada,
                'cantidad_disponible_padre' => $almacenPadre ? $cantidadDisponiblePadre : null,
                'cantidad_faltante' => $requiereCompra ? $cantidadFaltante : null,
                'requiere_compra' => $requiereCompra,
                'cantidad_aprobada' => $cantidadAprobada,
                'cantidad_entregada' => $cantidadEntregada,
                'unidad' => $itemData['unidad'] ?? $material->unidad_medida ?? 'unidad',
                'justificacion' => "Material necesario para {$data['motivo']}",
                'observaciones' => $index === 0 ? 'Item principal de la solicitud' : null,
            ]);
        }

        return $solicitud;
    }

    /**
     * Crea una solicitud que específicamente requiere compra
     * Asegura que los materiales solicitados tengan stock insuficiente en almacén padre
     */
    private function crearSolicitudConCompra(array $data, $materiales, array $itemsData, bool $aprobada = false): SolicitudMaterial
    {
        $solicitud = SolicitudMaterial::create([
            'numero_solicitud' => $data['numero_solicitud'],
            'cod_proy' => $data['proyecto']->cod_proy,
            'id_tarea' => $data['tarea']?->id_tarea,
            'solicitado_por' => $data['solicitado_por']->cod_empleado,
            'cargo_solicitante' => $data['solicitado_por']->cargo,
            'centro_costos' => 'CC-' . $data['proyecto']->cod_proy,
            'fecha_solicitud' => $data['fecha_solicitud'],
            'fecha_requerida' => $data['fecha_requerida'],
            'estado' => $data['estado'],
            'requiere_aprobacion' => true,
            'aprobada_por' => isset($data['aprobada_por']) ? $data['aprobada_por']?->cod_empleado : null,
            'fecha_aprobacion' => $data['fecha_aprobacion'] ?? null,
            'motivo' => $data['motivo'],
            'observaciones' => $data['observaciones'],
            'urgente' => $data['urgente'] ?? false,
        ]);

        // Buscar almacén asociado al proyecto
        $almacen = Almacen::where('cod_proy', $data['proyecto']->cod_proy)
            ->where('activo', true)
            ->first();

        // Obtener almacén padre si existe
        $almacenPadre = null;
        if ($almacen && $almacen->id_almacen_padre) {
            $almacenPadre = Almacen::where('id_almacen', $almacen->id_almacen_padre)
                ->where('activo', true)
                ->first();
        }

        // Crear items de la solicitud
        $materialesSeleccionados = $materiales->random(min(count($itemsData), $materiales->count()))->values();
        
        foreach ($itemsData as $index => $itemData) {
            if ($index >= $materialesSeleccionados->count()) {
                break;
            }

            $material = $materialesSeleccionados->get($index);
            $cantidadSolicitada = $itemData['cantidad'];
            $cantidadAprobada = $aprobada ? $cantidadSolicitada : null;
            $cantidadEntregada = 0;

            // Consultar stock en almacén padre si existe
            $cantidadDisponiblePadre = 0;
            $cantidadFaltante = 0;
            $requiereCompra = false;
            
            if ($almacenPadre) {
                $stockPadre = StockAlmacen::where('id_almacen', $almacenPadre->id_almacen)
                    ->where('id_material', $material->id_material)
                    ->first();
                
                if ($stockPadre) {
                    // Calcular cantidad disponible (disponible - reservada)
                    $cantidadDisponiblePadre = max(0, $stockPadre->cantidad_disponible - $stockPadre->cantidad_reservada);
                }
                
                // Para este seeder, forzamos que requiera compra limitando el stock disponible
                // Si el stock disponible es mayor o igual a lo solicitado, lo reducimos artificialmente
                if ($cantidadDisponiblePadre >= $cantidadSolicitada) {
                    // Reducir el stock disponible para forzar compra
                    // Dejamos solo un 20% de lo solicitado como disponible
                    $cantidadDisponiblePadre = max(0, $cantidadSolicitada * 0.2);
                    
                    // Actualizar el stock en la base de datos para reflejar esto
                    if ($stockPadre) {
                        $stockPadre->update([
                            'cantidad_disponible' => $cantidadDisponiblePadre + ($stockPadre->cantidad_reservada ?? 0)
                        ]);
                    }
                }
                
                // Calcular cantidad faltante
                if ($cantidadDisponiblePadre < $cantidadSolicitada) {
                    $cantidadFaltante = $cantidadSolicitada - $cantidadDisponiblePadre;
                    $requiereCompra = true;
                }
            } else {
                // Si no hay almacén padre, se asume que requiere compra completa
                $cantidadFaltante = $cantidadSolicitada;
                $requiereCompra = true;
            }

            SolicitudItem::create([
                'id_solicitud' => $solicitud->id_solicitud,
                'id_material' => $material->id_material,
                'id_lote' => null,
                'cantidad_solicitada' => $cantidadSolicitada,
                'cantidad_disponible_padre' => $almacenPadre ? $cantidadDisponiblePadre : null,
                'cantidad_faltante' => $requiereCompra ? $cantidadFaltante : null,
                'requiere_compra' => $requiereCompra,
                'cantidad_aprobada' => $cantidadAprobada,
                'cantidad_entregada' => $cantidadEntregada,
                'unidad' => $itemData['unidad'] ?? $material->unidad_medida ?? 'unidad',
                'justificacion' => "Material necesario para {$data['motivo']} - Requiere compra",
                'observaciones' => $index === 0 ? 'Item principal - requiere compra de materiales' : null,
            ]);
        }

        return $solicitud;
    }
}

