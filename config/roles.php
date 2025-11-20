<?php

return [
    'permissions' => [
        'inventory.view.central' => [
            'label' => 'Consultar inventario del almacén central',
            'module' => 'inventario',
        ],
        'inventory.view.project' => [
            'label' => 'Consultar inventario de proyectos y subalmacenes',
            'module' => 'inventario',
        ],
        'inventory.view.subwarehouses' => [
            'label' => 'Visualizar subalmacenes y frentes de obra',
            'module' => 'inventario',
        ],
        'inventory.movements.entries' => [
            'label' => 'Registrar entradas de material',
            'module' => 'inventario',
        ],
        'inventory.movements.exits' => [
            'label' => 'Registrar salidas de material',
            'module' => 'inventario',
        ],
        'inventory.movements.transfers' => [
            'label' => 'Registrar traslados entre almacenes',
            'module' => 'inventario',
        ],
        'inventory.movements.traceability' => [
            'label' => 'Asociar movimientos a proyecto/fase/frente',
            'module' => 'inventario',
        ],
        'inventory.consumption.history' => [
            'label' => 'Consultar consumo histórico de materiales',
            'module' => 'inventario',
        ],
        'materials.requests.create' => [
            'label' => 'Crear solicitudes de material',
            'module' => 'materiales',
        ],
        'materials.requests.approve' => [
            'label' => 'Aprobar o rechazar solicitudes',
            'module' => 'materiales',
        ],
        'materials.requests.deliver' => [
            'label' => 'Registrar entregas/avances de solicitudes',
            'module' => 'materiales',
        ],
        'dashboard.projects.overview' => [
            'label' => 'Ver panel general de proyectos',
            'module' => 'proyectos',
        ],
        'projects.detail.view' => [
            'label' => 'Ver detalle completo de proyectos',
            'module' => 'proyectos',
        ],
        'projects.compare' => [
            'label' => 'Comparar proyectos por avance/presupuesto/plazo',
            'module' => 'proyectos',
        ],
        'projects.manage.structure' => [
            'label' => 'Estructurar proyectos, fases e hitos',
            'module' => 'proyectos',
        ],
        'projects.materials.plan' => [
            'label' => 'Definir materiales previstos por fase',
            'module' => 'proyectos',
        ],
        'projects.my.view' => [
            'label' => 'Ver proyectos asignados',
            'module' => 'proyectos',
        ],
        'reports.material.summary' => [
            'label' => 'Consultar resúmenes de materiales y consumo',
            'module' => 'reportes',
        ],
        'reports.view' => [
            'label' => 'Consultar reportes y partes diarios',
            'module' => 'reportes',
        ],
        'reports.create' => [
            'label' => 'Registrar avances desde campo',
            'module' => 'reportes',
        ],
        'reports.approve' => [
            'label' => 'Aprobar o rechazar avances',
            'module' => 'reportes',
        ],
        'costs.review.adjust' => [
            'label' => 'Revisar/ajustar costos de proyecto',
            'module' => 'finanzas',
        ],
        'materials.requests.coordinate' => [
            'label' => 'Coordinar requerimientos por fase',
            'module' => 'materiales',
        ],
        'tracking.progress.monitor' => [
            'label' => 'Monitorear avance vs presupuesto/crono',
            'module' => 'proyectos',
        ],
        'incidents.review.project' => [
            'label' => 'Revisar incidencias de proyectos propios',
            'module' => 'incidencias',
        ],
        'incidents.review.impact' => [
            'label' => 'Revisar incidencias críticas e impacto',
            'module' => 'incidencias',
        ],
        'incidents.view' => [
            'label' => 'Ver incidencias registradas',
            'module' => 'incidencias',
        ],
        'incidents.create' => [
            'label' => 'Registrar incidencias de campo',
            'module' => 'incidencias',
        ],
        'tasks.assign' => [
            'label' => 'Asignar actividades al personal',
            'module' => 'tareas',
        ],
        'tasks.view' => [
            'label' => 'Consultar tareas y actividades',
            'module' => 'tareas',
        ],
        'materials.validate.usage' => [
            'label' => 'Validar uso de materiales reportados',
            'module' => 'materiales',
        ],
        'incidents.record.high' => [
            'label' => 'Registrar incidencias de alto impacto',
            'module' => 'incidencias',
        ],
        'reports.daily.close' => [
            'label' => 'Cerrar parte diario de obra',
            'module' => 'reportes',
        ],
        'mobile.tasks.execute' => [
            'label' => 'Registrar avance de tareas asignadas',
            'module' => 'movil',
        ],
        'mobile.photos.capture' => [
            'label' => 'Tomar fotos con ubicación',
            'module' => 'movil',
        ],
        'mobile.materials.report' => [
            'label' => 'Reportar consumo de materiales en campo',
            'module' => 'movil',
        ],
        'mobile.incidents.report' => [
            'label' => 'Reportar incidencias simples',
            'module' => 'movil',
        ],
        'mobile.tasks.view' => [
            'label' => 'Ver tareas asignadas en la app',
            'module' => 'movil',
        ],
        'admin.manage.employees' => [
            'label' => 'Administrar empleados/usuarios',
            'module' => 'admin',
        ],
    ],
    'roles' => [
        'adquisiciones' => [
            'name' => 'Adquisiciones',
            'description' => 'Gestiona inventario, entradas y traslados',
            'global' => true,
            'permissions' => [
                'inventory.view.central',
                'inventory.view.project',
                'inventory.view.subwarehouses',
                'inventory.movements.entries',
                'inventory.movements.exits',
                'inventory.movements.transfers',
                'inventory.movements.traceability',
                'inventory.consumption.history',
                'materials.requests.create',
                'materials.requests.approve',
                'materials.requests.deliver',
                'projects.detail.view',
                'reports.material.summary',
            ],
        ],
        'gerencia' => [
            'name' => 'Gerencia',
            'description' => 'Supervisa estados, costos e incidencias clave',
            'global' => true,
            'permissions' => [
                'admin.manage.employees',
                'dashboard.projects.overview',
                'projects.detail.view',
                'projects.compare',
                'reports.material.summary',
                'inventory.consumption.history',
                'incidents.review.impact',
                'reports.view',
            ],
        ],
        'responsable_proyecto' => [
            'name' => 'Responsable de proyecto',
            'description' => 'Define estructura, materiales y aprueba avances',
            'global' => true,
            'permissions' => [
                'projects.manage.structure',
                'projects.materials.plan',
                'reports.approve',
                'costs.review.adjust',
                'materials.requests.coordinate',
                'tracking.progress.monitor',
                'incidents.review.project',
                'projects.my.view',
                'reports.view',
                'incidents.view',
                'materials.requests.create',
                'materials.requests.approve',
            ],
        ],
        'supervisor' => [
            'name' => 'Supervisor',
            'description' => 'Gestiona frentes asignados y valida avances',
            'global' => false,
            'permissions' => [
                'projects.my.view',
                'tasks.assign',
                'tasks.view',
                'reports.view',
                'reports.approve',
                'materials.validate.usage',
                'incidents.record.high',
                'reports.daily.close',
                'inventory.view.project',
                'incidents.view',
            ],
        ],
        'personal_obra' => [
            'name' => 'Personal de obra',
            'description' => 'Registra avances y consumo en campo',
            'global' => false,
            'permissions' => [
                'projects.my.view',
                'mobile.tasks.execute',
                'mobile.tasks.view',
                'mobile.photos.capture',
                'mobile.materials.report',
                'mobile.incidents.report',
                'reports.create',
                'incidents.create',
            ],
        ],
    ],
];
