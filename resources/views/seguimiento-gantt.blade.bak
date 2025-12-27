<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Diagrama Gantt · Seguimiento</title>
    <link rel="stylesheet" href="https://unpkg.com/frappe-gantt@0.6.1/dist/frappe-gantt.css">
    <style>
        :root {
            color-scheme: light;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at top, #e0f2fe, #f8fafc 35%);
            font-family: 'Inter', 'Instrument Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #0f172a;
        }

        .gantt-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2.5rem 1.25rem 3rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .gantt-card {
            background: #fff;
            border-radius: 1.25rem;
            padding: 1.75rem;
            box-shadow: 0 20px 80px rgba(15, 23, 42, 0.08);
        }

        .gantt-header {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .gantt-header h1 {
            margin: 0;
            font-size: clamp(1.75rem, 3vw, 2.5rem);
        }

        .gantt-header p {
            margin: 0;
            color: #475569;
        }

        .gantt-filter {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .gantt-filter label {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #64748b;
        }

        .gantt-filter select {
            border-radius: 0.75rem;
            border: 1px solid #cbd5f5;
            padding: 0.65rem 0.75rem;
            background: #fff;
            font-size: 0.95rem;
            color: #0f172a;
            min-width: 260px;
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .gantt-chart-container {
            min-height: 420px;
            overflow: auto;
        }

        .gantt-empty {
            display: none;
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            background: #f1f5f9;
            color: #475569;
            font-size: 0.95rem;
        }

        .gantt-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .gantt-table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.5rem 0.25rem;
        }

        .gantt-table td {
            border-bottom: 1px solid #f1f5f9;
            padding: 0.65rem 0.25rem;
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 999px;
            font-size: 0.75rem;
            padding: 0.15rem 0.55rem;
            font-weight: 600;
        }

        .status-pendiente { background: rgba(249, 115, 22, 0.15); color: #c2410c; }
        .status-en-progreso { background: rgba(59, 130, 246, 0.15); color: #1d4ed8; }
        .status-finalizada { background: rgba(16, 185, 129, 0.15); color: #047857; }
        .status-bloqueada { background: rgba(239, 68, 68, 0.15); color: #b91c1c; }
        .status-default { background: rgba(99, 102, 241, 0.15); color: #4338ca; }

        .gantt-tooltip {
            padding: 0.75rem;
            border-radius: 0.65rem;
            background: #0f172a;
            color: #fff;
            min-width: 180px;
        }

        .gantt-tooltip h4 {
            margin: 0 0 0.35rem;
            font-size: 0.95rem;
        }

        .gantt-tooltip p {
            margin: 0.15rem 0;
            font-size: 0.8rem;
            color: #e2e8f0;
        }

        .bar.status-pendiente { fill: rgba(249, 115, 22, 0.9); }
        .bar.status-en-progreso { fill: rgba(59, 130, 246, 0.9); }
        .bar.status-finalizada { fill: rgba(16, 185, 129, 0.9); }
        .bar.status-bloqueada { fill: rgba(239, 68, 68, 0.9); }
        .bar.status-default { fill: rgba(99, 102, 241, 0.9); }
        .bar-progress { fill: rgba(15, 23, 42, 0.35); }

        @media (min-width: 768px) {
            .gantt-header {
                flex-direction: row;
                justify-content: space-between;
                align-items: flex-end;
            }

            .gantt-filter {
                align-items: flex-end;
            }
        }
    </style>
</head>
<body>
    @php
        $projectOptions = $projectOptions ?? [];
        $projectTasks = $projectTasks ?? [];
        $defaultProject = $defaultProject ?? (array_key_first($projectTasks) ?: null);
    @endphp
    <div class="gantt-wrapper">
        <div class="gantt-card">
            <div class="gantt-header">
                <div>
                    <p class="status-chip status-default" style="margin:0;">Seguimiento · Gantt</p>
                    <h1>Vista cronológica de tareas</h1>
                    <p>Selecciona un proyecto para recorrer su cronograma, duración y estado de cada tarea.</p>
                </div>
                <div class="gantt-filter">
                    <label for="gantt-project-filter">Proyecto</label>
                    <select id="gantt-project-filter" data-gantt-project {{ empty($projectOptions) ? 'disabled' : '' }}>
                        @forelse ($projectOptions as $option)
                            <option value="{{ $option['value'] }}" {{ $option['value'] == $defaultProject ? 'selected' : '' }}>
                                {{ $option['label'] }}
                            </option>
                        @empty
                            <option value="">Sin proyectos disponibles</option>
                        @endforelse
                    </select>
                </div>
            </div>
            <div class="gantt-chart-container">
                <div id="gantt-chart"></div>
            </div>
            <div class="gantt-empty" data-gantt-empty>
                No hay tareas con fechas programadas para este proyecto.
            </div>
        </div>

        <div class="gantt-card">
            <h2 style="margin-top:0;margin-bottom:1rem;font-size:1.25rem;">Detalle de tareas</h2>
            <div class="gantt-table-wrapper">
                <table class="gantt-table">
                    <thead>
                        <tr>
                            <th>Tarea</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Días</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody data-gantt-table>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>
    <script>
        (() => {
            const tasksByProject = @json($projectTasks);
            const defaultProject = @json($defaultProject);
            const projectFilter = document.querySelector('[data-gantt-project]');
            const emptyState = document.querySelector('[data-gantt-empty]');
            const tableBody = document.querySelector('[data-gantt-table]');
            const chartContainer = document.getElementById('gantt-chart');
            let ganttInstance = null;

            const statusClass = (status) => {
                const normalized = (status || 'default').toLowerCase().replace(/[\s_]+/g, '-');
                return ['pendiente', 'en-progreso', 'finalizada', 'bloqueada'].includes(normalized)
                    ? normalized
                    : 'default';
            };

            const formatDate = (value) => {
                if (!value) {
                    return '-';
                }

                const parsed = new Date(value);
                if (Number.isNaN(parsed.getTime())) {
                    return value;
                }

                return parsed.toLocaleDateString('es-BO', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                });
            };

            const toggleEmptyState = (show) => {
                if (!emptyState) {
                    return;
                }
                emptyState.style.display = show ? 'block' : 'none';
            };

            const hydrateTable = (tasks) => {
                if (!tableBody) {
                    return;
                }

                if (!tasks.length) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="5" style="text-align:center;color:#94a3b8;padding:1rem 0;">
                                No hay tareas programadas con fechas definidas.
                            </td>
                        </tr>
                    `;
                    return;
                }

                tableBody.innerHTML = tasks.map((task) => {
                    const klass = statusClass(task.status);
                    const label = task.status || 'Sin estado';
                    return `
                        <tr>
                            <td>${task.name ?? 'Tarea'} (#${task.id})</td>
                            <td>${formatDate(task.start)}</td>
                            <td>${formatDate(task.end)}</td>
                            <td>${task.duration_days ?? '-'}</td>
                            <td>
                                <span class="status-chip status-${klass}">
                                    ${label}
                                </span>
                            </td>
                        </tr>
                    `;
                }).join('');
            };

            const buildTasksForProject = (projectCode) => {
                if (!projectCode || !tasksByProject[projectCode]) {
                    return [];
                }

                return (tasksByProject[projectCode].tasks ?? []).map((task) => ({
                    ...task,
                    progress: Number(task.progress ?? 0),
                    custom_class: `status-${statusClass(task.status)}`,
                }));
            };

            const renderGantt = (tasks, projectName) => {
                chartContainer.innerHTML = '';

                if (!tasks.length) {
                    toggleEmptyState(true);
                    ganttInstance = null;
                    return;
                }

                toggleEmptyState(false);

                ganttInstance = new Gantt(chartContainer, tasks, {
                    view_mode: 'Week',
                    language: 'es',
                    popup_trigger: 'click',
                    custom_popup_html: (task) => `
                        <div class="gantt-tooltip">
                            <h4>${task.name}</h4>
                            <p>Proyecto: ${projectName || '-'}</p>
                            <p>Inicio: ${formatDate(task.start)}</p>
                            <p>Fin: ${formatDate(task.end)}</p>
                            <p>Avance: ${task.progress ?? 0}%</p>
                        </div>
                    `,
                });
            };

            const renderProject = (projectCode) => {
                const dataset = buildTasksForProject(projectCode);
                const projectName = tasksByProject[projectCode]?.name ?? '';
                hydrateTable(dataset);
                renderGantt(dataset, projectName);
            };

            if (projectFilter) {
                projectFilter.addEventListener('change', (event) => {
                    renderProject(event.target.value);
                });
            }

            const initialProject = defaultProject || projectFilter?.value || Object.keys(tasksByProject)[0];
            if (initialProject && projectFilter) {
                projectFilter.value = initialProject;
            }
            renderProject(initialProject);
        })();
    </script>
</body>
</html>
