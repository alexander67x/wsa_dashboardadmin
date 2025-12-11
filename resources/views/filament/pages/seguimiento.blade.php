@php
    $viewCopy = [
        'curve' => [
            'title' => 'Curva de seguimiento',
            'description' => 'Compara el avance planificado vs. real por semana.',
            'highlights' => [
                'Detalle semanal por proyecto',
                'Curva acumulada y barras comparativas',
            ],
        ],
        'calendar' => [
            'title' => 'Calendario',
            'description' => 'Visualiza tareas en una línea de tiempo interactiva.',
            'highlights' => [
                'Eventos con colores por estado',
                'Atajos para abrir y editar tareas',
            ],
        ],
    ];
    $currentCopy = $viewCopy[$viewMode] ?? $viewCopy['curve'];
@endphp

<x-filament::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Seguimiento</x-slot>

            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $currentCopy['title'] }}</p>
                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ $currentCopy['description'] }}</p>
                </div>
                <div class="w-full max-w-xs space-y-2 text-sm font-medium text-gray-600 dark:text-gray-300">
                    <label for="tracking-view-selector" class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Cambiar vista
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select id="tracking-view-selector" wire:model.live="viewMode">
                            <option value="curve">Curva de seguimiento</option>
                            <option value="calendar">Calendario</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ($currentCopy['highlights'] as $highlight)
                    <x-filament::badge color="gray" size="sm">
                        {{ $highlight }}
                    </x-filament::badge>
                @endforeach
            </div>
        </x-filament::section>

        @if ($viewMode === 'curve')
            @include('seguimiento.partials.curve', [
                'projectOptions' => $projectOptions,
                'projectSeries' => $projectSeries,
                'selectedProject' => $defaultProject,
            ])
        @else
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                @livewire('app.filament.widgets.seguimiento-calendar-widget', [], key('seguimiento-calendar'))
            </div>
        @endif
    </div>
</x-filament::page>
