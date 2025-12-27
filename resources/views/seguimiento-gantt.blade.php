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

        html,
        body {
            max-width: 100%;
            overflow-x: hidden;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at top, #e0f2fe, #f8fafc 35%);
            font-family: 'Inter', 'Instrument Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #0f172a;
        }

        .gantt-wrapper {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2.5rem 1.25rem 3rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            box-sizing: border-box;
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
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .gantt-filter-group {
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
            min-width: 220px;
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .gantt-layout {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .gantt-sidebar {
            flex: 0 0 260px;
            border-radius: 1rem;
            background: linear-gradient(to bottom, #ecfdf3, #f0fdf4);
            border: 1px solid #bbf7d0;
            padding: 1rem;
        }

        .gantt-sidebar-title {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #15803d;
            margin-bottom: 0.5rem;
        }

        .gantt-sidebar-phase {
            border-radius: 0.85rem;
            padding: 0.6rem 0.75rem;
            background: rgba(22, 163, 74, 0.06);
            border: 1px solid rgba(22, 163, 74, 0.14);
            margin-bottom: 0.5rem;
        }

        .gantt-sidebar-phase-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: #065f46;
        }

        .gantt-sidebar-phase-dates {
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 0.1rem;
        }

        .gantt-sidebar-milestones {
            list-style: none;
            padding: 0.4rem 0 0;
            margin: 0;
        }

        .gantt-sidebar-milestone {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            color: #0f172a;
            padding: 0.2rem 0;
        }

        .gantt-sidebar-milestone-dot {
            width: 0.4rem;
            height: 0.4rem;
            border-radius: 999px;
            background: #22c55e;
        }

        .gantt-sidebar-milestone--critical .gantt-sidebar-milestone-dot {
            background: #ef4444;
        }

        .gantt-sidebar-empty {
            font-size: 0.8rem;
            color: #64748b;
        }

        .gantt-main {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .gantt-chart-container {
            position: relative;
            min-height: 420px;
            max-height: 640px;
            overflow-x: auto;
            overflow-y: auto;
        }

        #gantt-chart {
            width: 100%;
            max-width: 100%;
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

        .status-pendiente { background: rgba(249, 115, 22, 0.15); color: #b45309; }
        .status-en-progreso { background: rgba(99, 102, 241, 0.15); color: #4338ca; }
        .status-finalizada { background: rgba(34, 197, 94, 0.16); color: #15803d; }
        .status-bloqueada { background: rgba(239, 68, 68, 0.15); color: #b91c1c; }
        .status-default { background: rgba(15, 23, 42, 0.06); color: #0f172a; }

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

        /* Colores tipo Filament por fase (cubos bien diferenciados) */
        .bar.phase-0 { fill: #6366f1; }  /* primary */
        .bar.phase-1 { fill: #22c55e; }  /* success */
        .bar.phase-2 { fill: #eab308; }  /* warning */
        .bar.phase-3 { fill: #0ea5e9; }  /* info */
        .bar.phase-4 { fill: #ef4444; }  /* danger */
        .bar.phase-5 { fill: #14b8a6; }  /* teal extra */
        .bar.status-pendiente { fill: rgba(249, 115, 22, 0.9); }
        .bar.status-en-progreso { fill: rgba(99, 102, 241, 0.9); }
        .bar.status-finalizada { fill: rgba(34, 197, 94, 0.95); }
        .bar.status-bloqueada { fill: rgba(239, 68, 68, 0.9); }
        .bar.status-default { fill: rgba(15, 23, 42, 0.75); }
        .bar-progress { fill: rgba(15, 23, 42, 0.35); }

        @media (min-width: 900px) {
            .gantt-layout {
                flex-direction: row;
                align-items: stretch;
            }

            .gantt-sidebar {
                max-width: 260px;
            }
        }

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

        @media (prefers-color-scheme: dark) {
            body {
                background: radial-gradient(circle at top, #020617, #020617 45%);
                color: #e5e7eb;
            }

            .gantt-card {
                background: #020617;
                box-shadow: 0 20px 80px rgba(15, 23, 42, 0.6);
            }

            .gantt-header p {
                color: #9ca3af;
            }

            .gantt-sidebar {
                background: linear-gradient(to bottom, #022c22, #064e3b);
                border-color: #047857;
            }

            .gantt-sidebar-phase {
                background: rgba(16, 185, 129, 0.16);
                border-color: rgba(16, 185, 129, 0.4);
            }

            .gantt-sidebar-phase-name {
                color: #ecfdf5;
            }

            .gantt-sidebar-phase-dates {
                color: #a7f3d0;
            }

            .gantt-sidebar-milestone {
                color: #ecfdf5;
            }

            .gantt-sidebar-empty {
                color: #a7f3d0;
            }

            .gantt-card,
            .gantt-table {
                color: #e5e7eb;
            }

            .gantt-table th {
                color: #9ca3af;
                border-bottom-color: #1f2937;
            }

            .gantt-table td {
                border-bottom-color: #111827;
            }

            .gantt-empty {
                background: #020617;
                color: #9ca3af;
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
                    <h1>Vista cronológica por fases e hitos</h1>
                    <p>Selecciona un proyecto para revisar sus fases, hitos y tareas.</p>
                </div>
                <div class="gantt-filter">
                    <div class="gantt-filter-group">
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
                    <div class="gantt-filter-group">
                        <label for="gantt-zoom">Vista</label>
                        <select id="gantt-zoom" data-gantt-zoom>
                            <option value="Month">Vista general (meses)</option>
                            <option value="Week" selected>Detalle semanal</option>
                            <option value="Day">Zoom diario</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="gantt-layout">
                <aside class="gantt-sidebar" data-gantt-sidebar>
                    <!-- Se rellena desde JavaScript -->
                </aside>

                <div class="gantt-main">
                    <div class="gantt-chart-container">
                        <div id="gantt-chart"></div>
                    </div>
                    <div class="gantt-empty" data-gantt-empty>
                        No hay tareas con fechas programadas para este proyecto.
                    </div>
                </div>
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
                            <th>Fase</th>
                            <th>Hito</th>
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
            const sidebar = document.querySelector('[data-gantt-sidebar]');
            const emptyState = document.querySelector('[data-gantt-empty]');
            const tableBody = document.querySelector('[data-gantt-table]');
            const chartContainer = document.getElementById('gantt-chart');
            const zoomSelect = document.querySelector('[data-gantt-zoom]');

            let ganttInstance = null;
            let currentViewMode = 'Week';

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

            const formatMonthDay = (value) => {
                if (!value) {
                    return null;
                }

                const parsed = new Date(value);
                if (Number.isNaN(parsed.getTime())) {
                    return null;
                }

                return parsed.toLocaleDateString('es-BO', {
                    day: '2-digit',
                    month: 'short',
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
                            <td colspan="7" style="text-align:center;color:#94a3b8;padding:1rem 0;">
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
                            <td>${task.fase ?? ''}</td>
                            <td>${task.hito ?? ''}</td>
                            <td>
                                <span class="status-chip status-${klass}">
                                    ${label}
                                </span>
                            </td>
                        </tr>
                    `;
                }).join('');
            };

            const getPhaseKey = (task) => {
                const rawName = String(task.name ?? '').trim();
                const phaseId = task.fase_id ?? task.id_fase ?? null;
                const phaseName = task.fase ?? null;

                let derivedName = null;
                if (rawName.includes('-')) {
                    const prefix = rawName.split('-')[0].trim();
                    if (prefix && /fase\s*\d*/i.test(prefix)) {
                        derivedName = prefix;
                    }
                }

                if (phaseId !== null && phaseId !== undefined) {
                    return {
                        key: `id:${phaseId}`,
                        name: phaseName || derivedName || `Fase ${phaseId}`,
                    };
                }

                if (phaseName) {
                    return {
                        key: `name:${phaseName}`,
                        name: phaseName,
                    };
                }

                if (derivedName) {
                    return {
                        key: `name:${derivedName}`,
                        name: derivedName,
                    };
                }

                return { key: null, name: null };
            };

            const getMilestoneKey = (task) => {
                const rawName = String(task.name ?? '').trim();
                const milestoneId = task.hito_id ?? task.id_hito ?? null;
                const milestoneName = task.hito ?? null;

                let derivedName = null;
                if (rawName.includes('-')) {
                    const parts = rawName.split('-').map((part) => part.trim());
                    const candidate = parts.length > 1 ? parts[1] : null;
                    if (candidate && /hito/i.test(candidate)) {
                        derivedName = candidate;
                    }
                }

                if (milestoneId !== null && milestoneId !== undefined) {
                    return {
                        key: `id:${milestoneId}`,
                        name: milestoneName || derivedName || `Hito ${milestoneId}`,
                    };
                }

                if (milestoneName) {
                    return {
                        key: `name:${milestoneName}`,
                        name: milestoneName,
                    };
                }

                if (derivedName) {
                    return {
                        key: `name:${derivedName}`,
                        name: derivedName,
                    };
                }

                return { key: null, name: null };
            };

            const buildPhaseHierarchy = (projectCode) => {
                const project = tasksByProject[projectCode];
                if (!project) {
                    return [];
                }

                const phasesById = {};

                const rawPhases = Array.isArray(project.phases) ? project.phases : [];
                rawPhases.forEach((phase, index) => {
                    const numericId = phase.id ?? phase.id_fase ?? phase.fase_id ?? null;
                    const key = numericId !== null && numericId !== undefined
                        ? `id:${numericId}`
                        : `index:${index}`;

                    if (!phasesById[key]) {
                        phasesById[key] = {
                            id: key,
                            name: phase.name ?? phase.nombre ?? phase.nombre_fase ?? `Fase ${index + 1}`,
                            start: phase.start ?? phase.fecha_inicio ?? null,
                            end: phase.end ?? phase.fecha_fin ?? null,
                            order: typeof phase.order === 'number' ? phase.order : phase.orden ?? index,
                            milestones: [],
                        };
                    }
                });

                const milestonesById = {};

                const rawMilestones = Array.isArray(project.milestones) ? project.milestones : [];
                rawMilestones.forEach((milestone) => {
                    const numericId = milestone.id ?? milestone.id_hito;
                    if (numericId === null || numericId === undefined) {
                        return;
                    }

                    const milestoneKey = `id:${numericId}`;
                    const phaseKey = milestone.fase_id ?? milestone.id_fase ?? null;
                    const phaseId = phaseKey !== null && phaseKey !== undefined ? `id:${phaseKey}` : null;

                    const entry = {
                        id: milestoneKey,
                        phaseId,
                        name: milestone.name ?? milestone.titulo ?? `Hito ${numericId}`,
                        date: milestone.date ?? milestone.fecha_hito ?? milestone.fecha_final_hito ?? null,
                        isCritical: Boolean(milestone.is_critical ?? milestone.es_critico),
                    };

                    milestonesById[milestoneKey] = entry;

                    if (phaseId && phasesById[phaseId]) {
                        phasesById[phaseId].milestones.push(entry);
                    }
                });

                const rawTasks = Array.isArray(project.tasks) ? project.tasks : [];
                rawTasks.forEach((task) => {
                    const phaseInfo = getPhaseKey(task);
                    const milestoneInfo = getMilestoneKey(task);
                    const phaseId = phaseInfo.key;
                    const milestoneId = milestoneInfo.key;

                    if (phaseId && !phasesById[phaseId]) {
                        phasesById[phaseId] = {
                            id: phaseId,
                            name: phaseInfo.name ?? task.fase ?? 'Fase',
                            start: task.start ?? null,
                            end: task.end ?? null,
                            order: Object.keys(phasesById).length + 1,
                            milestones: [],
                        };
                    }

                    if (milestoneId && !milestonesById[milestoneId]) {
                        milestonesById[milestoneId] = {
                            id: milestoneId,
                            phaseId,
                            name: milestoneInfo.name ?? task.hito ?? 'Hito',
                            date: task.start ?? null,
                            isCritical: false,
                        };
                    }

                    if (phaseId && milestoneId && phasesById[phaseId]) {
                        const phase = phasesById[phaseId];
                        const alreadyLinked = phase.milestones.some((milestone) => milestone.id === milestoneId);
                        if (!alreadyLinked && milestonesById[milestoneId]) {
                            phase.milestones.push(milestonesById[milestoneId]);
                        }
                    }

                    if (phaseId && phasesById[phaseId]) {
                        const phase = phasesById[phaseId];
                        if (task.start && (!phase.start || new Date(task.start) < new Date(phase.start))) {
                            phase.start = task.start;
                        }
                        if (task.end && (!phase.end || new Date(task.end) > new Date(phase.end))) {
                            phase.end = task.end;
                        }
                    }
                });

                return Object.values(phasesById)
                    .map((phase) => ({
                        ...phase,
                        milestones: (phase.milestones ?? []).sort((a, b) => {
                            const aDate = a.date ? new Date(a.date) : null;
                            const bDate = b.date ? new Date(b.date) : null;

                            if (aDate && bDate) {
                                return aDate - bDate;
                            }

                            if (aDate) {
                                return -1;
                            }

                            if (bDate) {
                                return 1;
                            }

                            return (a.name || '').localeCompare(b.name || '');
                        }),
                    }))
                    .sort((a, b) => {
                        const orderDiff = (a.order ?? 0) - (b.order ?? 0);
                        if (orderDiff !== 0) {
                            return orderDiff;
                        }

                        const aStart = a.start ? new Date(a.start) : null;
                        const bStart = b.start ? new Date(b.start) : null;

                        if (aStart && bStart) {
                            return aStart - bStart;
                        }

                        if (aStart) {
                            return -1;
                        }

                        if (bStart) {
                            return 1;
                        }

                        return (a.name || '').localeCompare(b.name || '');
                    });
            };

            const renderSidebar = (projectCode) => {
                if (!sidebar) {
                    return;
                }

                const phases = buildPhaseHierarchy(projectCode);

                if (!phases.length) {
                    sidebar.innerHTML = `
                        <p class="gantt-sidebar-title">Fases e hitos</p>
                        <p class="gantt-sidebar-empty">
                            No hay fases ni hitos definidos para este proyecto, pero puedes seguir usando el diagrama de Gantt.
                        </p>
                    `;
                    return;
                }

                sidebar.innerHTML = [
                    '<p class="gantt-sidebar-title">Fases e hitos</p>',
                    phases.map((phase) => {
                        const dates = [formatMonthDay(phase.start), formatMonthDay(phase.end)].filter(Boolean);
                        const dateRange = dates.length === 2
                            ? `${dates[0]} - ${dates[1]}`
                            : (dates[0] ?? '');

                        const milestonesHtml = (phase.milestones ?? []).map((milestone) => {
                            const milestoneDate = formatMonthDay(milestone.date);
                            const isCriticalClass = milestone.isCritical ? ' gantt-sidebar-milestone--critical' : '';

                            return `
                                <li class="gantt-sidebar-milestone${isCriticalClass}">
                                    <span class="gantt-sidebar-milestone-dot"></span>
                                    <div>
                                        <div>${milestone.name}</div>
                                        ${milestoneDate ? `<div style="font-size:0.75rem;color:#94a3b8;">${milestoneDate}</div>` : ''}
                                    </div>
                                </li>
                            `;
                        }).join('');

                        return `
                            <div class="gantt-sidebar-phase">
                                <div class="gantt-sidebar-phase-name">${phase.name}</div>
                                ${dateRange ? `<div class="gantt-sidebar-phase-dates">${dateRange}</div>` : ''}
                                ${milestonesHtml
                                    ? `<ul class="gantt-sidebar-milestones">${milestonesHtml}</ul>`
                                    : ''}
                            </div>
                        `;
                    }).join(''),
                ].join('');
            };

            const buildTasksForProject = (projectCode) => {
                const project = tasksByProject[projectCode];
                if (!projectCode || !project) {
                    return [];
                }

                const phases = buildPhaseHierarchy(projectCode);
                const phaseIndexById = {};
                phases.forEach((phase, index) => {
                    phaseIndexById[phase.id] = index;
                });

                const rawTasks = Array.isArray(project.tasks) ? project.tasks : [];

                return rawTasks.map((task) => {
                    const phaseInfo = getPhaseKey(task);
                    const phaseIndex = phaseInfo.key && Object.prototype.hasOwnProperty.call(phaseIndexById, phaseInfo.key)
                        ? phaseIndexById[phaseInfo.key]
                        : null;

                    const classes = [`status-${statusClass(task.status)}`];
                    if (phaseIndex !== null) {
                        classes.push(`phase-${phaseIndex}`);
                    }

                    return {
                        ...task,
                        progress: Number(task.progress ?? 0),
                        custom_class: classes.join(' '),
                    };
                });
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
                    view_mode: currentViewMode,
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
                renderSidebar(projectCode);
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

            if (zoomSelect) {
                zoomSelect.addEventListener('change', (event) => {
                    currentViewMode = event.target.value || 'Week';
                    if (ganttInstance) {
                        ganttInstance.change_view_mode(currentViewMode);
                    }
                });
            }
        })();
    </script>
</body>
</html>
