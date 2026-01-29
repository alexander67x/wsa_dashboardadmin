@php
    $items = $items ?? collect();
    $deliveries = $deliveries ?? collect();
    $receipts = $receipts ?? collect();
    $historial = $historial ?? collect();
    $almacenesOrigen = $almacenesOrigen ?? collect();
    $almacenesDestino = $almacenesDestino ?? collect();
    $stockProyecto = $stockProyecto ?? collect();
    $almacenProyecto = $almacenProyecto ?? null;
    $almacenOrigenFallback = $almacenOrigenFallback ?? null;
    $fotosRecepcion = $fotosRecepcion ?? collect();
    $reportesUso = $reportesUso ?? collect();

    $totalSolicitado = $items->sum('cantidad_solicitada');
    $totalEntregado = $items->sum('cantidad_entregada');
    $totalIngresado = $receipts->sum('cantidad_entregada');

    $eventoCompra = $historial->firstWhere('tipo_evento', 'aprobada_con_compra');
    $eventoDespacho = $historial->firstWhere('tipo_evento', 'entregado');
    $eventoEnviado = $historial->firstWhere('tipo_evento', 'enviado');
    $eventoRecepcion = $historial->firstWhere('tipo_evento', 'recibida');

    $ultimoDespacho = $deliveries->max('fecha_entrega');
    $ultimaRecepcion = $deliveries->max('fecha_recepcion');

    $tieneCompra = $record->requiere_compra ?? false;
    $tieneDespachos = $deliveries->isNotEmpty();
    $estadoDespacho = in_array($record->estado ?? '', ['enviado', 'recibida'], true);
    $tieneRecepciones = $receipts->isNotEmpty();

    $materialNames = $items->mapWithKeys(function ($item) {
        $nombre = $item->material?->nombre_producto ?? "Material ID: {$item->id_material}";
        return [$item->id_material => $nombre];
    });

    $detalleSolicitado = $items
        ->map(function ($item) use ($materialNames) {
            $nombre = $materialNames[$item->id_material] ?? "Material ID: {$item->id_material}";
            $cantidad = number_format((float) $item->cantidad_solicitada, 2);
            return "{$nombre} ({$cantidad})";
        })
        ->values();

    $detalleDespacho = $estadoDespacho
        ? ($tieneDespachos
            ? $deliveries
                ->groupBy('id_material')
                ->map(function ($group, $materialId) use ($materialNames) {
                    $nombre = $materialNames[$materialId] ?? "Material ID: {$materialId}";
                    $cantidad = number_format((float) $group->sum('cantidad_entregada'), 2);
                    return "{$nombre} ({$cantidad})";
                })
                ->values()
            : $items
                ->map(function ($item) use ($materialNames) {
                    $nombre = $materialNames[$item->id_material] ?? "Material ID: {$item->id_material}";
                    $cantidadBase = $item->cantidad_aprobada ?? $item->cantidad_solicitada;
                    $cantidad = number_format((float) $cantidadBase, 2);
                    return "{$nombre} ({$cantidad})";
                })
                ->values())
        : collect();

    $cantidadDespacho = $estadoDespacho
        ? ($tieneDespachos
            ? $totalEntregado
            : $items->sum(function ($item) {
                return (float) ($item->cantidad_aprobada ?? $item->cantidad_solicitada ?? 0);
            }))
        : 0;

    $almacenOrigenTexto = $estadoDespacho
        ? ($tieneDespachos ? ($almacenesOrigen->implode(', ') ?: 'N/D') : ($almacenOrigenFallback?->nombre ?? 'N/D'))
        : 'N/D';

    $fechaDespacho = $estadoDespacho
        ? ($tieneDespachos
            ? ($ultimoDespacho?->format('d/m/Y H:i') ?? $eventoDespacho?->fecha_evento?->format('d/m/Y H:i'))
            : ($eventoEnviado?->fecha_evento?->format('d/m/Y H:i') ?? $record->updated_at?->format('d/m/Y H:i')))
        : 'N/D';

    $detalleRecepcion = $receipts
        ->groupBy('id_material')
        ->map(function ($group, $materialId) use ($materialNames) {
            $nombre = $materialNames[$materialId] ?? "Material ID: {$materialId}";
            $cantidad = number_format((float) $group->sum('cantidad_entregada'), 2);
            return "{$nombre} ({$cantidad})";
        })
        ->values();

    $detalleStockProyecto = $stockProyecto
        ->map(function ($stock) {
            $nombre = $stock->material?->nombre_producto ?? "Material ID: {$stock->id_material}";
            $cantidad = number_format((float) $stock->cantidad_disponible, 2);
            $alerta = number_format((float) $stock->cantidad_minima_alerta, 2);
            $ubicacion = $stock->ubicacion_fisica ?: 'N/D';
            return [
                'nombre' => $nombre,
                'cantidad' => $cantidad,
                'alerta' => $alerta,
                'ubicacion' => $ubicacion,
            ];
        })
        ->values();

    $reportePrincipal = $reportesUso->first();
    $numeroReporte = $reportePrincipal?->id_reporte
        ? 'REP-' . str_pad((string) $reportePrincipal->id_reporte, 3, '0', STR_PAD_LEFT)
        : 'N/D';
    $detalleUsoObra = ($reportePrincipal?->materiales ?? collect())
        ->map(function ($material) {
            $nombre = $material->material?->nombre_producto ?? "Material ID: {$material->id_material}";
            $cantidad = number_format((float) $material->cantidad_usada, 2);
            return "{$nombre} ({$cantidad})";
        })
        ->values();
    $detalleTareasUso = $reportesUso->map(function ($reporte) {
        $tarea = $reporte->tarea?->titulo ?? 'Tarea sin nombre';
        $fecha = $reporte->fecha_reporte?->format('d/m/Y') ?? 'N/D';
        $materiales = ($reporte->materiales ?? collect())->map(function ($material) {
            $nombre = $material->material?->nombre_producto ?? "Material ID: {$material->id_material}";
            $cantidad = number_format((float) $material->cantidad_usada, 2);
            return "{$nombre} ({$cantidad})";
        })->implode(', ');
        return [
            'reporte' => $reporte->id_reporte ? 'REP-' . str_pad((string) $reporte->id_reporte, 3, '0', STR_PAD_LEFT) : 'N/D',
            'tarea' => $tarea,
            'fecha' => $fecha,
            'materiales' => $materiales ?: 'Sin materiales',
        ];
    })->values();

    $projectUrl = $record?->proyecto
        ? \App\Filament\Resources\Proyectos\ProyectoResource::getUrl('view', ['record' => $record->proyecto->cod_proy])
        : null;
@endphp

<x-filament::page>
    <div class="traceability-layout">
        <header class="traceability-header">
            <div class="traceability-header__text">
                <h1>Trazabilidad de Materiales del Proyecto</h1>
                <p>
                    Visualiza el recorrido completo de los materiales en el proyecto
                    {{ $record->proyecto?->nombre_ubicacion ?? $record->proyecto?->nombre_proyecto ?? $record->cod_proy ?? 'N/D' }}.
                </p>
            </div>
            @if ($projectUrl)
                <a class="traceability-button" href="{{ $projectUrl }}">Volver a Proyecto</a>
            @endif
        </header>

        <section class="traceability-steps">
            <div class="traceability-step">
                <div class="traceability-step__icon traceability-step__icon--blue">
                    <x-filament::icon icon="heroicon-o-clipboard-document-list" class="h-6 w-6" />
                </div>
                <h3>Solicitud de materiales</h3>
                <div class="traceability-card">
                    <div><span>Solicitud N°</span><strong>{{ $record->numero_solicitud ?? 'N/D' }}</strong></div>
                    <div><span>Proyecto</span><strong>{{ $record->proyecto?->nombre_ubicacion ?? $record->proyecto?->nombre_proyecto ?? 'N/D' }}</strong></div>
                    <div>
                        <details class="traceability-toggle">
                            <summary>Materiales solicitados</summary>
                            <ul>
                                @forelse ($detalleSolicitado as $linea)
                                    <li>{{ $linea }}</li>
                                @empty
                                    <li>Sin materiales</li>
                                @endforelse
                            </ul>
                        </details>
                    </div>
                    <div><span>Fecha</span><strong>{{ optional($record->fecha_solicitud)->format('d/m/Y H:i') ?? 'N/D' }}</strong></div>
                    <div><span>Estado</span><strong>{{ ucfirst($record->estado ?? 'N/D') }}</strong></div>
                    <div><span>Responsable</span><strong>{{ $record->solicitadoPor?->nombre_completo ?? 'N/D' }}</strong></div>
                </div>
            </div>

            <div class="traceability-step">
                <div class="traceability-step__icon traceability-step__icon--blue">
                    <x-filament::icon icon="heroicon-o-truck" class="h-6 w-6" />
                </div>
                <h3>Despacho desde almacén central</h3>
                <div class="traceability-card">
                    <details class="traceability-toggle">
                        <summary>Materiales despachados</summary>
                        <ul>
                            @forelse ($detalleDespacho as $linea)
                                <li>{{ $linea }}</li>
                            @empty
                                <li>Sin despachos registrados</li>
                            @endforelse
                        </ul>
                    </details>
                    <div><span>Cantidad despachada</span><strong>{{ number_format($cantidadDespacho, 2) }}</strong></div>
                    <div><span>Almacén origen</span><strong>{{ $almacenOrigenTexto }}</strong></div>
                    <div><span>Fecha</span><strong>{{ $fechaDespacho ?? 'N/D' }}</strong></div>
                    <div><span>Compra requerida</span><strong>{{ $tieneCompra ? 'Sí' : 'No' }}</strong></div>
                </div>
            </div>

            <div class="traceability-step">
                <div class="traceability-step__icon traceability-step__icon--green">
                    <x-filament::icon icon="heroicon-o-clipboard-document-check" class="h-6 w-6" />
                </div>
                <h3>Ingreso al almacén de proyecto</h3>
                <div class="traceability-card">
                    <div><span>Cantidad ingresada</span><strong>{{ number_format($totalIngresado, 2) }}</strong></div>
                    <div><span>Almacén proyecto</span><strong>{{ $almacenProyecto?->nombre ?? 'N/D' }}</strong></div>
                    <div><span>Fecha</span><strong>{{ $ultimaRecepcion?->format('d/m/Y H:i') ?? $eventoRecepcion?->fecha_evento?->format('d/m/Y H:i') ?? 'N/D' }}</strong></div>
                    <div><span>Estado</span><strong>{{ $tieneRecepciones ? 'Registrado' : 'Pendiente' }}</strong></div>
                    <details class="traceability-toggle">
                        <summary>Materiales ingresados</summary>
                        <ul>
                            @forelse ($detalleRecepcion as $linea)
                                <li>{{ $linea }}</li>
                            @empty
                                <li>Sin recepciones</li>
                            @endforelse
                        </ul>
                    </details>
                    <details class="traceability-toggle">
                        <summary>Evidencia de llegada</summary>
                        @if ($fotosRecepcion->isNotEmpty())
                            <div class="traceability-photo-grid">
                                @foreach ($fotosRecepcion as $fotoUrl)
                                    <a href="{{ $fotoUrl }}" target="_blank" rel="noopener">
                                        <img src="{{ $fotoUrl }}" alt="Evidencia de llegada">
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="traceability-muted">Sin evidencia registrada</p>
                        @endif
                    </details>
                </div>
            </div>

            <div class="traceability-step">
                <div class="traceability-step__icon traceability-step__icon--green">
                    <x-filament::icon icon="heroicon-o-building-storefront" class="h-6 w-6" />
                </div>
                <h3>Almacén de proyecto</h3>
                <div class="traceability-card">
                    @forelse ($detalleStockProyecto as $stock)
                        <div class="traceability-stock">
                            <strong>{{ $stock['nombre'] }}</strong>
                            <div><span>Cantidad disponible</span><strong>{{ $stock['cantidad'] }}</strong></div>
                            <div><span>Alerta</span><strong>{{ $stock['alerta'] }}</strong></div>
                            <div><span>Ubicación</span><strong>{{ $stock['ubicacion'] }}</strong></div>
                        </div>
                    @empty
                        <p class="traceability-muted">Sin inventario registrado para este proyecto.</p>
                    @endforelse
                </div>
            </div>

            <div class="traceability-step">
                <div class="traceability-step__icon traceability-step__icon--orange">
                    <x-filament::icon icon="heroicon-o-wrench-screwdriver" class="h-6 w-6" />
                </div>
                <h3>Uso en obra</h3>
                <div class="traceability-card">
                    <div><span>Reporte</span><strong>{{ $numeroReporte }}</strong></div>
                    <div><span>Tarea / fase</span><strong>{{ $reportePrincipal?->tarea?->titulo ?? 'N/D' }}</strong></div>
                    <div><span>Fecha</span><strong>{{ $reportePrincipal?->fecha_reporte?->format('d/m/Y') ?? 'N/D' }}</strong></div>
                    <details class="traceability-toggle">
                        <summary>Materiales usados</summary>
                        <ul>
                            @forelse ($detalleUsoObra as $linea)
                                <li>{{ $linea }}</li>
                            @empty
                                <li>Sin materiales reportados</li>
                            @endforelse
                        </ul>
                    </details>
                    <details class="traceability-toggle">
                        <summary>Tareas finalizadas</summary>
                        <ul>
                            @forelse ($detalleTareasUso as $detalle)
                                <li>
                                    <strong>{{ $detalle['tarea'] }}</strong> · {{ $detalle['reporte'] }} · {{ $detalle['fecha'] }}<br>
                                    <span>{{ $detalle['materiales'] }}</span>
                                </li>
                            @empty
                                <li>No hay reportes aprobados con tareas finalizadas.</li>
                            @endforelse
                        </ul>
                    </details>
                </div>
            </div>
        </section>

        @if ($projectUrl)
            <div class="traceability-footer">
                <a class="traceability-button" href="{{ $projectUrl }}">Volver a Proyecto</a>
            </div>
        @endif
    </div>
</x-filament::page>

@push('styles')
    <style>
        .traceability-layout {
            padding: 1.5rem 0 2rem;
            color: #1f2937;
        }

        .traceability-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            text-align: center;
        }

        .traceability-header__text {
            flex: 1;
        }

        .traceability-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }

        .traceability-header p {
            margin: 0.5rem 0 0;
            font-size: 1rem;
            color: #4b5563;
        }

        .traceability-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 999px;
            background: #f9c534;
            color: #3f2d00;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 8px 18px rgba(249, 197, 52, 0.4);
        }

        .traceability-steps {
            margin-top: 2.5rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            position: relative;
        }

        .traceability-steps::before {
            content: '';
            position: absolute;
            top: 34px;
            left: 5%;
            right: 5%;
            height: 6px;
            background: linear-gradient(90deg, #7dc4ff, #7dc4ff, #6edc82, #6edc82, #f9c534);
            border-radius: 999px;
            z-index: 0;
        }

        .traceability-step {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .traceability-step h3 {
            margin: 0.5rem 0 1rem;
            font-size: 1rem;
            font-weight: 700;
            color: #1f2937;
        }

        .traceability-step__icon {
            width: 54px;
            height: 54px;
            margin: 0 auto;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid #e5e7eb;
            background: #fff;
            color: #1f2937;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.15);
        }

        .traceability-step__icon--blue {
            background: #d7ecff;
        }

        .traceability-step__icon--green {
            background: #d9f7d7;
        }

        .traceability-step__icon--orange {
            background: #ffe5b5;
        }

        .traceability-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 1rem;
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.08);
            text-align: left;
            min-height: 320px;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .traceability-card span {
            display: block;
            font-size: 0.8rem;
            color: #6b7280;
        }

        .traceability-card strong {
            font-size: 0.95rem;
            color: #1f2937;
            font-weight: 600;
        }

        .traceability-card ul {
            margin: 0.25rem 0 0;
            padding-left: 1.2rem;
            color: #374151;
            font-size: 0.9rem;
        }

        .traceability-toggle {
            border-top: 1px dashed #e5e7eb;
            padding-top: 0.5rem;
        }

        .traceability-toggle summary {
            cursor: pointer;
            font-weight: 600;
            color: #1f2937;
            list-style: none;
        }

        .traceability-toggle summary::-webkit-details-marker {
            display: none;
        }

        .traceability-toggle summary::after {
            content: '+';
            float: right;
            font-weight: 700;
            color: #6b7280;
        }

        .traceability-toggle[open] summary::after {
            content: '−';
        }

        .traceability-photo-grid {
            margin-top: 0.5rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 0.75rem;
        }

        .traceability-photo-grid img {
            width: 100%;
            height: 96px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            display: block;
        }

        .traceability-stock {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .traceability-photo {
            margin-top: 0.5rem;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .traceability-photo img {
            display: block;
            width: 100%;
            height: auto;
        }

        .traceability-muted {
            font-size: 0.85rem;
            color: #6b7280;
            margin: 0.25rem 0 0;
        }

        .traceability-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }

        @media (max-width: 768px) {
            .traceability-steps::before {
                display: none;
            }

            .traceability-header {
                text-align: left;
            }

            .traceability-card {
                min-height: auto;
            }
        }
    </style>
@endpush
