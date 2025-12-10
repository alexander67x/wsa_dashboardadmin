<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seguimiento de Proyectos</title>
</head>
<body class="seguimiento-body">
    @include('seguimiento.partials.curve', [
        'standalone' => true,
        'projectOptions' => $projectOptions ?? [],
        'projectSeries' => $projectSeries ?? [],
        'selectedProject' => $defaultProject ?? null,
    ])
</body>
</html>
