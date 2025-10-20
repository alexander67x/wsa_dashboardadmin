@props([
    'tarea', // App\Models\Tarea
    'empleados' => collect(), // Collection de Empleado para asignar
])

@php
    $estadoColor = match($tarea->estado) {
        'pendiente' => 'gray',
        'en_proceso' => 'warning',
        'finalizada' => 'success',
        default => 'gray',
    };

    $nombre = $tarea->responsable?->nombre_completo;
    $iniciales = '—';
    if ($nombre) {
        $partes = preg_split('/\s+/', trim($nombre));
        $ini = '';
        foreach ($partes as $i => $p) {
            if ($i > 1) break;
            $ini .= mb_substr($p, 0, 1);
        }
        $iniciales = mb_strtoupper($ini);
    }
@endphp

<div
    class="kb-card cursor-move"
    draggable="true"
    @dragstart="event.dataTransfer.setData('text/plain', {{ $tarea->id_tarea }}); event.dataTransfer.effectAllowed='move'"
>
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
            <div class="text-[13px] font-medium text-gray-900 dark:text-gray-100 leading-5">{{ $tarea->titulo }}</div>
            <div class="flex items-center gap-2">
                <x-filament::badge :color="$estadoColor" size="xs">{{ $tarea->estado }}</x-filament::badge>
            </div>
        </div>
        <div class="shrink-0 flex gap-1.5">
            <button type="button" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800" wire:click="deleteTarea({{ $tarea->id_tarea }})">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-gray-500"><path d="M6.75 7.5h10.5M9 7.5l.75-1.5h4.5L15 7.5m-6 0v9.75A1.5 1.5 0 0010.5 18.75h3A1.5 1.5 0 0015 17.25V7.5"/></svg>
            </button>
        </div>
    </div>

    <div class="mt-3 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2 min-w-0">
            <div class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 text-[10px] font-semibold">
                {{ $iniciales }}
            </div>
            <div class="truncate text-xs text-gray-600 dark:text-gray-300">
                {{ $tarea->responsable?->nombre_completo ?? 'Sin responsable' }}
            </div>
        </div>
        <select
            class="rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-2 py-1.5 text-xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
            wire:change="assignResponsable({{ $tarea->id_tarea }}, parseInt($event.target.value)||null)"
        >
            <option value="">Sin responsable</option>
            @foreach($empleados as $e)
                <option value="{{ $e->cod_empleado }}" @selected($tarea->responsable_id === $e->cod_empleado)>
                    {{ $e->nombre_completo }}
                </option>
            @endforeach
        </select>
    </div>
</div>


