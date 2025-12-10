<x-filament::page>
    @include('seguimiento.partials.curve', [
        'projectOptions' => $projectOptions,
        'projectSeries' => $projectSeries,
        'selectedProject' => $defaultProject,
    ])
</x-filament::page>
