@php
    $standalone = $standalone ?? false;
    $chartElementId = $chartElementId ?? 'curveChart';
    $projectOptions = $projectOptions ?? [];
    $seriesCollection = collect($projectSeries ?? []);
    $seriesIndexed = $seriesCollection
        ->filter(fn ($serie) => is_array($serie) && isset($serie['codigo']))
        ->mapWithKeys(fn ($serie) => [$serie['codigo'] => $serie])
        ->toArray();
    $selectedProject = $selectedProject ?? array_key_first($seriesIndexed);
    if (! $selectedProject || ! array_key_exists($selectedProject, $seriesIndexed)) {
        $selectedProject = array_key_first($seriesIndexed);
    }
    $initialSeries = $selectedProject && isset($seriesIndexed[$selectedProject])
        ? $seriesIndexed[$selectedProject]
        : (reset($seriesIndexed) ?: null);
    $initialDetail = $initialSeries['detail'] ?? [];
@endphp

@once
    <style>
        .seguimiento-body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at top, #e0f2fe, #f8fafc 35%);
            font-family: 'Inter', 'Instrument Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #0f172a;
        }

        .seguimiento-standalone-wrapper {
            padding: 2.5rem 1.25rem 3rem;
        }

        .seguimiento-layout {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .seguimiento-card {
            background: #fff;
            border-radius: 1.25rem;
            padding: 1.75rem;
            box-shadow: 0 20px 80px rgba(15, 23, 42, 0.1);
        }

        .seguimiento-header-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            background: rgba(16, 185, 129, 0.15);
            color: #047857;
        }

        .seguimiento-title {
            font-size: clamp(1.75rem, 3vw, 2.5rem);
            margin: 0.35rem 0;
        }

        .seguimiento-lead {
            margin: 0;
            color: #475569;
            font-size: 1rem;
        }

        .seguimiento-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1rem;
            align-items: flex-end;
        }

        .seguimiento-filter label {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 0.25rem;
            display: block;
        }

        .seguimiento-filter select {
            min-width: 260px;
            border-radius: 0.65rem;
            border: 1px solid #cbd5f5;
            padding: 0.65rem 0.75rem;
            background: #fff;
            font-size: 0.95rem;
            color: #0f172a;
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .seguimiento-filter select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .seguimiento-table-wrapper {
            overflow-x: auto;
        }

        .seguimiento-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        .seguimiento-table th,
        .seguimiento-table td {
            padding: 0.75rem 0.5rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .seguimiento-table th {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
        }

        .seguimiento-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.2rem 0.45rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .seguimiento-tag.success {
            background: rgba(16, 185, 129, 0.15);
            color: #047857;
        }

        .seguimiento-tag.warn {
            background: rgba(249, 115, 22, 0.15);
            color: #c2410c;
        }

        .seguimiento-empty {
            text-align: center;
            padding: 2rem;
            color: #94a3b8;
        }

        .seguimiento-card canvas {
            width: 100% !important;
            min-height: 320px;
        }

        @media (max-width: 720px) {
            .seguimiento-card {
                padding: 1.25rem;
            }

            .seguimiento-filter select {
                width: 100%;
                min-width: 0;
            }

            .seguimiento-table {
                font-size: 0.85rem;
            }
        }
    </style>
@endonce

@if ($standalone)
    <div class="seguimiento-standalone-wrapper">
@endif
    <div class="seguimiento-layout" wire:ignore>
        <header>
            <p class="seguimiento-header-tag">Curva de seguimiento</p>
            <h1 class="seguimiento-title">Control de avance por proyecto</h1>
            <p class="seguimiento-lead">
                Filtra por proyecto para ver la curva semanal entre lo planificado, lo ejecutado y el peso del total del proyecto.
            </p>

            <div class="seguimiento-filter">
                <div>
                    <label for="seguimiento-project-filter">Proyecto</label>
                    <select id="seguimiento-project-filter" data-project-filter {{ empty($seriesIndexed) ? 'disabled' : '' }}>
                        @forelse ($projectOptions as $option)
                            <option value="{{ $option['value'] }}" {{ $option['value'] == $selectedProject ? 'selected' : '' }}>
                                {{ $option['label'] }}
                            </option>
                        @empty
                            <option value="">Sin proyectos disponibles</option>
                        @endforelse
                    </select>
                </div>
            </div>
        </header>

        <div class="seguimiento-card">
            <canvas id="{{ $chartElementId }}" aria-label="Curva de seguimiento" role="img"></canvas>
            <div class="seguimiento-empty" data-chart-empty {{ $initialSeries ? 'style=display:none;' : '' }}>
                Aún no hay datos semanales suficientes para graficar.
            </div>
        </div>

        <div class="seguimiento-card">
            <div class="seguimiento-table-wrapper">
                <table class="seguimiento-table">
                    <thead>
                        <tr>
                            <th>Semana</th>
                            <th>% Planificado</th>
                            <th>% Avance real</th>
                            <th>Tareas planificadas</th>
                            <th>Tareas completadas</th>
                            <th>% Cumplimiento semanal</th>
                            <th>% Avance total</th>
                        </tr>
                    </thead>
                    <tbody data-tracking-table>
                        @forelse ($initialDetail as $detalle)
                            <tr>
                                <td>{{ $detalle['semana'] }}</td>
                                <td>{{ number_format($detalle['planificado'] ?? 0, 1) }}%</td>
                                <td>
                                    {{ $detalle['avance_real'] !== null ? number_format($detalle['avance_real'], 1) . '%' : 'Sin reporte' }}
                                </td>
                                <td>{{ $detalle['tareas_planificadas'] }}</td>
                                <td>{{ $detalle['tareas_completadas'] }}</td>
                                <td>
                                    <span class="seguimiento-tag {{ ($detalle['cumplimiento_tareas'] ?? 0) >= 70 ? 'success' : 'warn' }}">
                                        {{ number_format($detalle['cumplimiento_tareas'] ?? 0, 1) }}%
                                    </span>
                                </td>
                                <td>{{ number_format($detalle['avance_total'] ?? 0, 1) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="seguimiento-empty">Selecciona un proyecto con planificación semanal para ver el detalle.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@if ($standalone)
    </div>
@endif

@once
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
@endonce
<script>
    (() => {
        const bootSeguimientoCurve = () => {
            if (!window.Chart) {
                return;
            }

            const ctx = document.getElementById(@json($chartElementId));
            if (!ctx) {
                return;
            }

            const projectSeries = @json($seriesIndexed);
            const projectFilter = document.querySelector('[data-project-filter]');
            const tableBody = document.querySelector('[data-tracking-table]');
            const emptyState = document.querySelector('[data-chart-empty]');
            let currentProject = @json($selectedProject);
            let chartInstance = ctx._seguimientoChartInstance ?? null;

            if (chartInstance) {
                chartInstance.destroy();
                chartInstance = null;
                ctx._seguimientoChartInstance = null;
            }

            const findInitialProject = () => {
                if (currentProject && projectSeries[currentProject]) {
                    return currentProject;
                }

                const keys = Object.keys(projectSeries);
                return keys.length ? keys[0] : null;
            };

            const hydrateTable = (serie) => {
                if (!tableBody) {
                    return;
                }

                const details = Array.isArray(serie?.detail) ? serie.detail : [];
                if (!details.length) {
                    tableBody.innerHTML = '<tr><td colspan="7" class="seguimiento-empty">No hay datos semanales para este proyecto.</td></tr>';
                    return;
                }

                tableBody.innerHTML = details.map((detalle) => {
                    const cumplimiento = Number(detalle.cumplimiento_tareas ?? 0);
                    const tagClass = cumplimiento >= 70 ? 'success' : 'warn';
                    const avanceReal = detalle.avance_real !== null && detalle.avance_real !== undefined
                        ? `${Number(detalle.avance_real).toFixed(1)}%`
                        : 'Sin reporte';

                    return `
                        <tr>
                            <td>${detalle.semana ?? ''}</td>
                            <td>${Number(detalle.planificado ?? 0).toFixed(1)}%</td>
                            <td>${avanceReal}</td>
                            <td>${detalle.tareas_planificadas ?? 0}</td>
                            <td>${detalle.tareas_completadas ?? 0}</td>
                            <td>
                                <span class="seguimiento-tag ${tagClass}">
                                    ${cumplimiento.toFixed(1)}%
                                </span>
                            </td>
                            <td>${Number(detalle.avance_total ?? 0).toFixed(1)}%</td>
                        </tr>
                    `;
                }).join('');
            };

            const toggleEmptyState = (show) => {
                if (!emptyState) {
                    return;
                }
                emptyState.style.display = show ? 'block' : 'none';
            };

            const buildDatasets = (serie) => ([
                {
                    type: 'bar',
                    label: 'Cumplimiento semanal (tareas)',
                    data: serie?.weekly ?? [],
                    backgroundColor: 'rgba(37, 99, 235, 0.65)',
                    borderRadius: 8,
                    maxBarThickness: 28,
                    order: 2,
                },
                {
                    type: 'bar',
                    label: 'Avance total del proyecto',
                    data: serie?.total ?? [],
                    backgroundColor: 'rgba(14, 165, 233, 0.35)',
                    borderColor: '#0ea5e9',
                    borderWidth: 1,
                    borderRadius: 8,
                    maxBarThickness: 28,
                    order: 2,
                },
                {
                    type: 'line',
                    label: 'Curva de avance real',
                    data: serie?.curve ?? [],
                    borderColor: '#f97316',
                    backgroundColor: 'transparent',
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.35,
                    order: 3,
                    fill: false,
                },
            ]);

            const renderChart = (projectCode) => {
                const serie = projectSeries[projectCode];
                hydrateTable(serie);

                if (!serie || !serie.labels?.length) {
                    if (chartInstance) {
                        chartInstance.destroy();
                        chartInstance = null;
                        ctx._seguimientoChartInstance = null;
                    }
                    toggleEmptyState(true);
                    return;
                }

                toggleEmptyState(false);

                if (!chartInstance) {
                    chartInstance = new window.Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: serie.labels,
                            datasets: buildDatasets(serie),
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: {
                                        callback: (value) => `${value}%`,
                                    },
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.2)',
                                    },
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.1)',
                                    },
                                },
                            },
                            interaction: {
                                intersect: false,
                                mode: 'index',
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        boxWidth: 8,
                                    },
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => {
                                            const value = context.parsed?.y ?? context.parsed ?? 0;
                                            return `${context.dataset.label}: ${Number(value).toFixed(1)}%`;
                                        },
                                    },
                                },
                            },
                        },
                    });
                } else {
                    chartInstance.data.labels = serie.labels ?? [];
                    chartInstance.data.datasets = buildDatasets(serie);
                    chartInstance.update();
                }

                ctx._seguimientoChartInstance = chartInstance;
            };

            const initialProject = findInitialProject();
            if (!initialProject) {
                toggleEmptyState(true);
                hydrateTable(null);
                if (projectFilter) {
                    projectFilter.disabled = true;
                }
                return;
            }

            currentProject = initialProject;
            renderChart(initialProject);

            if (projectFilter) {
                if (projectFilter._seguimientoChangeHandler) {
                    projectFilter.removeEventListener('change', projectFilter._seguimientoChangeHandler);
                }

                projectFilter._seguimientoChangeHandler = (event) => {
                    const nextProject = event.target.value;
                    currentProject = nextProject;
                    renderChart(nextProject);
                };

                projectFilter.addEventListener('change', projectFilter._seguimientoChangeHandler);
            }
        };

        const initSeguimientoCurve = () => {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootSeguimientoCurve, { once: true });
            } else {
                bootSeguimientoCurve();
            }
        };

        initSeguimientoCurve();
        document.addEventListener('livewire:navigated', bootSeguimientoCurve);
    })();
</script>
