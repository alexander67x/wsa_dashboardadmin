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
            --seguimiento-text: #0f172a;
            --seguimiento-muted: #475569;
            --seguimiento-card-bg: #ffffff;
            --seguimiento-shadow: 0 20px 80px rgba(15, 23, 42, 0.1);
            --seguimiento-header-tag-bg: rgba(16, 185, 129, 0.15);
            --seguimiento-header-tag-text: #047857;
            --seguimiento-filter-bg: #ffffff;
            --seguimiento-filter-border: #cbd5f5;
            --seguimiento-filter-focus: #2563eb;
            --seguimiento-table-border: #e2e8f0;
            --seguimiento-table-head: #94a3b8;
            --seguimiento-empty: #94a3b8;
            --seguimiento-tag-success-bg: rgba(16, 185, 129, 0.15);
            --seguimiento-tag-success-text: #047857;
            --seguimiento-tag-warn-bg: rgba(249, 115, 22, 0.15);
            --seguimiento-tag-warn-text: #c2410c;
            --seguimiento-chart-bar: rgba(37, 99, 235, 0.65);
            --seguimiento-chart-bar-alt: rgba(14, 165, 233, 0.35);
            --seguimiento-chart-bar-alt-border: #0ea5e9;
            --seguimiento-chart-line: #f97316;
            --seguimiento-chart-line-planned: #6366f1;
            --seguimiento-chart-grid: rgba(148, 163, 184, 0.2);
            --seguimiento-chart-grid-soft: rgba(148, 163, 184, 0.1);
            --seguimiento-chart-tick: #475569;
            --seguimiento-chart-legend: #334155;
            color: var(--seguimiento-text);
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .seguimiento-card {
            background: var(--seguimiento-card-bg);
            border-radius: 1.25rem;
            padding: 1.75rem;
            box-shadow: var(--seguimiento-shadow);
        }

        .seguimiento-header-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            background: var(--seguimiento-header-tag-bg);
            color: var(--seguimiento-header-tag-text);
        }

        .seguimiento-title {
            font-size: clamp(1.75rem, 3vw, 2.5rem);
            margin: 0.35rem 0;
        }

        .seguimiento-lead {
            margin: 0;
            color: var(--seguimiento-muted);
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
            color: var(--seguimiento-muted);
            margin-bottom: 0.25rem;
            display: block;
        }

        .seguimiento-filter select {
            min-width: 260px;
            border-radius: 0.65rem;
            border: 1px solid var(--seguimiento-filter-border);
            padding: 0.65rem 0.75rem;
            background: var(--seguimiento-filter-bg);
            font-size: 0.95rem;
            color: var(--seguimiento-text);
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .seguimiento-filter select:focus {
            outline: none;
            border-color: var(--seguimiento-filter-focus);
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
            border-bottom: 1px solid var(--seguimiento-table-border);
            white-space: nowrap;
        }

        .seguimiento-table th {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--seguimiento-table-head);
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
            background: var(--seguimiento-tag-success-bg);
            color: var(--seguimiento-tag-success-text);
        }

        .seguimiento-tag.warn {
            background: var(--seguimiento-tag-warn-bg);
            color: var(--seguimiento-tag-warn-text);
        }

        .seguimiento-empty {
            text-align: center;
            padding: 2rem;
            color: var(--seguimiento-empty);
        }

        .seguimiento-card canvas {
            width: 100% !important;
            min-height: 320px;
        }

        .dark .seguimiento-body {
            background: radial-gradient(circle at top, #0b1220, #020617 45%);
            color: #e2e8f0;
        }

        .dark .seguimiento-layout {
            --seguimiento-text: #e2e8f0;
            --seguimiento-muted: #94a3b8;
            --seguimiento-card-bg: #0f172a;
            --seguimiento-shadow: 0 20px 60px rgba(2, 6, 23, 0.6);
            --seguimiento-header-tag-bg: rgba(16, 185, 129, 0.2);
            --seguimiento-header-tag-text: #6ee7b7;
            --seguimiento-filter-bg: #0b1220;
            --seguimiento-filter-border: #1e293b;
            --seguimiento-filter-focus: #38bdf8;
            --seguimiento-table-border: #1e293b;
            --seguimiento-table-head: #94a3b8;
            --seguimiento-empty: #94a3b8;
            --seguimiento-tag-success-bg: rgba(16, 185, 129, 0.2);
            --seguimiento-tag-success-text: #6ee7b7;
            --seguimiento-tag-warn-bg: rgba(251, 146, 60, 0.2);
            --seguimiento-tag-warn-text: #fdba74;
            --seguimiento-chart-bar: rgba(56, 189, 248, 0.65);
            --seguimiento-chart-bar-alt: rgba(14, 165, 233, 0.4);
            --seguimiento-chart-bar-alt-border: #38bdf8;
            --seguimiento-chart-line: #fb923c;
            --seguimiento-chart-line-planned: #a5b4fc;
            --seguimiento-chart-grid: rgba(148, 163, 184, 0.3);
            --seguimiento-chart-grid-soft: rgba(148, 163, 184, 0.15);
            --seguimiento-chart-tick: #cbd5f5;
            --seguimiento-chart-legend: #e2e8f0;
        }

        @media (prefers-color-scheme: dark) {
            .seguimiento-body {
                background: radial-gradient(circle at top, #0b1220, #020617 45%);
                color: #e2e8f0;
            }

            .seguimiento-body .seguimiento-layout {
                --seguimiento-text: #e2e8f0;
                --seguimiento-muted: #94a3b8;
                --seguimiento-card-bg: #0f172a;
                --seguimiento-shadow: 0 20px 60px rgba(2, 6, 23, 0.6);
                --seguimiento-header-tag-bg: rgba(16, 185, 129, 0.2);
                --seguimiento-header-tag-text: #6ee7b7;
                --seguimiento-filter-bg: #0b1220;
                --seguimiento-filter-border: #1e293b;
                --seguimiento-filter-focus: #38bdf8;
                --seguimiento-table-border: #1e293b;
                --seguimiento-table-head: #94a3b8;
                --seguimiento-empty: #94a3b8;
                --seguimiento-tag-success-bg: rgba(16, 185, 129, 0.2);
                --seguimiento-tag-success-text: #6ee7b7;
                --seguimiento-tag-warn-bg: rgba(251, 146, 60, 0.2);
                --seguimiento-tag-warn-text: #fdba74;
                --seguimiento-chart-bar: rgba(56, 189, 248, 0.65);
                --seguimiento-chart-bar-alt: rgba(14, 165, 233, 0.4);
                --seguimiento-chart-bar-alt-border: #38bdf8;
                --seguimiento-chart-line: #fb923c;
                --seguimiento-chart-line-planned: #a5b4fc;
                --seguimiento-chart-grid: rgba(148, 163, 184, 0.3);
                --seguimiento-chart-grid-soft: rgba(148, 163, 184, 0.15);
                --seguimiento-chart-tick: #cbd5f5;
                --seguimiento-chart-legend: #e2e8f0;
            }
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
            const layout = document.querySelector('.seguimiento-layout');
            let currentProject = @json($selectedProject);
            let chartInstance = ctx._seguimientoChartInstance ?? null;

            if (chartInstance) {
                chartInstance.destroy();
                chartInstance = null;
                ctx._seguimientoChartInstance = null;
            }

            const getThemeTokens = () => {
                const fallback = {
                    bar: 'rgba(37, 99, 235, 0.65)',
                    barAlt: 'rgba(14, 165, 233, 0.35)',
                    barAltBorder: '#0ea5e9',
                    line: '#f97316',
                    linePlanned: '#6366f1',
                    grid: 'rgba(148, 163, 184, 0.2)',
                    gridSoft: 'rgba(148, 163, 184, 0.1)',
                    tick: '#475569',
                    legend: '#334155',
                };

                if (!layout) {
                    return fallback;
                }

                const styles = getComputedStyle(layout);
                const value = (name, fallbackValue) =>
                    (styles.getPropertyValue(name) || fallbackValue).trim();

                return {
                    bar: value('--seguimiento-chart-bar', fallback.bar),
                    barAlt: value('--seguimiento-chart-bar-alt', fallback.barAlt),
                    barAltBorder: value('--seguimiento-chart-bar-alt-border', fallback.barAltBorder),
                    line: value('--seguimiento-chart-line', fallback.line),
                    linePlanned: value('--seguimiento-chart-line-planned', fallback.linePlanned),
                    grid: value('--seguimiento-chart-grid', fallback.grid),
                    gridSoft: value('--seguimiento-chart-grid-soft', fallback.gridSoft),
                    tick: value('--seguimiento-chart-tick', fallback.tick),
                    legend: value('--seguimiento-chart-legend', fallback.legend),
                };
            };

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

            const buildDatasets = (serie, theme) => ([
                {
                    type: 'bar',
                    label: 'Cumplimiento semanal (tareas)',
                    data: serie?.weekly ?? [],
                    backgroundColor: theme.bar,
                    borderRadius: 8,
                    maxBarThickness: 28,
                    order: 2,
                },
                {
                    type: 'bar',
                    label: 'Avance total del proyecto',
                    data: serie?.total ?? [],
                    backgroundColor: theme.barAlt,
                    borderColor: theme.barAltBorder,
                    borderWidth: 1,
                    borderRadius: 8,
                    maxBarThickness: 28,
                    order: 2,
                },
                {
                    type: 'line',
                    label: 'Curva de avance real',
                    data: serie?.curve ?? [],
                    borderColor: theme.line,
                    backgroundColor: 'transparent',
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.35,
                    order: 3,
                    fill: false,
                },
                {
                    type: 'line',
                    label: 'Curva de avance planificado',
                    data: serie?.planned ?? [],
                    borderColor: theme.linePlanned,
                    backgroundColor: 'transparent',
                    borderWidth: 3,
                    borderDash: [6, 6],
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.25,
                    order: 3,
                    fill: false,
                },
            ]);

            const buildOptions = (theme) => ({
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: (value) => `${value}%`,
                            color: theme.tick,
                        },
                        grid: {
                            color: theme.grid,
                        },
                    },
                    x: {
                        ticks: {
                            color: theme.tick,
                        },
                        grid: {
                            color: theme.gridSoft,
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
                            color: theme.legend,
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
            });

            const renderChart = (projectCode) => {
                const serie = projectSeries[projectCode];
                hydrateTable(serie);
                const theme = getThemeTokens();

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
                            datasets: buildDatasets(serie, theme),
                        },
                        options: buildOptions(theme),
                    });
                } else {
                    chartInstance.data.labels = serie.labels ?? [];
                    chartInstance.data.datasets = buildDatasets(serie, theme);
                    chartInstance.options = buildOptions(theme);
                    chartInstance.update();
                }

                ctx._seguimientoChartInstance = chartInstance;
            };

            const handleThemeChange = () => {
                if (!chartInstance) {
                    return;
                }

                const theme = getThemeTokens();
                chartInstance.data.datasets = buildDatasets(projectSeries[currentProject], theme);
                chartInstance.options = buildOptions(theme);
                chartInstance.update('none');
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

            if (window.matchMedia) {
                const media = window.matchMedia('(prefers-color-scheme: dark)');
                media.addEventListener('change', handleThemeChange);
            }

            const themeObserver = new MutationObserver(handleThemeChange);
            themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
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
