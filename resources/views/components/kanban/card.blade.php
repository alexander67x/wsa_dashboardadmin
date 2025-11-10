@props([
    'tarea', // App\Models\Tarea
    'empleados' => collect(), // Collection de Empleado para asignar
])

@php
    // Estado colors and labels
    $estadoConfig = match($tarea->estado) {
        'pendiente' => ['color' => 'gray', 'label' => 'Pendiente', 'icon' => 'clock'],
        'en_proceso' => ['color' => 'warning', 'label' => 'En Proceso', 'icon' => 'play'],
        'en_pausa' => ['color' => 'gray', 'label' => 'En Pausa', 'icon' => 'pause'],
        'en_revision' => ['color' => 'info', 'label' => 'En Revisión', 'icon' => 'eye'],
        'finalizada' => ['color' => 'success', 'label' => 'Finalizada', 'icon' => 'check'],
        'cancelada' => ['color' => 'danger', 'label' => 'Cancelada', 'icon' => 'x'],
        default => ['color' => 'gray', 'label' => $tarea->estado, 'icon' => 'question'],
    };

    // Responsable info
    $responsable = $tarea->responsable;
    $responsableNombre = $responsable?->nombre_completo;
    $responsableIniciales = '—';
    
    if ($responsableNombre) {
        $partes = preg_split('/\s+/', trim($responsableNombre));
        $iniciales = '';
        foreach ($partes as $i => $parte) {
            if ($i >= 2) break;
            $iniciales .= mb_substr($parte, 0, 1);
        }
        $responsableIniciales = mb_strtoupper($iniciales);
    }

    // Prioridad config
    $prioridad = $tarea->prioridad ?? 'media';
    $prioridadConfig = match($prioridad) {
        'alta' => [
            'class' => 'priority-alta',
            'badgeClass' => 'priority-alta-badge',
            'label' => 'Alta',
            'icon' => 'arrow-up'
        ],
        'media' => [
            'class' => 'priority-media',
            'badgeClass' => 'priority-media-badge',
            'label' => 'Media',
            'icon' => 'minus'
        ],
        'baja' => [
            'class' => 'priority-baja',
            'badgeClass' => 'priority-baja-badge',
            'label' => 'Baja',
            'icon' => 'arrow-down'
        ],
        default => [
            'class' => 'priority-media',
            'badgeClass' => 'priority-media-badge',
            'label' => ucfirst($prioridad),
            'icon' => 'minus'
        ],
    };

    // Fecha fin info
    $fechaFin = $tarea->fecha_fin;
    $isOverdue = $fechaFin && $fechaFin->isPast() && !in_array($tarea->estado, ['finalizada', 'cancelada']);
    $daysUntilDue = $fechaFin ? now()->diffInDays($fechaFin, false) : null;
    $isDueSoon = $daysUntilDue !== null && $daysUntilDue <= 3 && $daysUntilDue >= 0 && !$isOverdue;
@endphp

<div
    x-data="{ isDragging: false }"
    class="kanban-card {{ $prioridadConfig['class'] }}"
    :class="{ 'dragging': isDragging }"
    draggable="true"
    @dragstart="
        isDragging = true;
        event.dataTransfer.setData('text/plain', {{ $tarea->id_tarea }});
        event.dataTransfer.effectAllowed = 'move';
    "
    @dragend="isDragging = false"
>
    <!-- Card Header: Title and Actions -->
    <div class="flex items-start justify-between gap-2 mb-3">
        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 leading-tight flex-1 min-w-0 line-clamp-2">
            {{ $tarea->titulo }}
        </h4>
        <button 
            type="button" 
            class="shrink-0 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 
                   transition-all opacity-0 group-hover:opacity-100"
            wire:click="deleteTarea({{ $tarea->id_tarea }})"
            onclick="return confirm('¿Estás seguro de que deseas eliminar esta tarea?\n\nTítulo: {{ addslashes($tarea->titulo) }}')"
            title="Eliminar tarea"
        >
            <svg class="w-4 h-4 text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors" 
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </button>
    </div>

    <!-- Description (if exists) -->
    @if($tarea->descripcion)
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-3 line-clamp-2 leading-relaxed">
            {{ Str::limit($tarea->descripcion, 100) }}
        </p>
    @endif

    <!-- Badges: Priority and Status -->
    <div class="flex items-center gap-2 mb-3 flex-wrap">
        <span class="priority-badge {{ $prioridadConfig['badgeClass'] }}">
            {{ $prioridadConfig['label'] }}
        </span>
        <x-filament::badge :color="$estadoConfig['color']" size="xs">
            {{ $estadoConfig['label'] }}
        </x-filament::badge>
    </div>

    <!-- Due Date with Alerts -->
    @if($fechaFin)
        <div class="flex items-center gap-2 mb-3">
            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="text-xs text-gray-600 dark:text-gray-400">
                {{ $fechaFin->format('d/m/Y') }}
            </span>
            @if($isOverdue)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium 
                             bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Vencida
                </span>
            @elseif($isDueSoon)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium 
                             bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Próxima
                </span>
            @endif
        </div>
    @endif

    <!-- Footer: Responsable and Assign Selector -->
    <div class="flex items-center justify-between gap-2 pt-3 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-2 min-w-0 flex-1">
            @if($responsableNombre)
                <div class="inline-flex h-8 w-8 items-center justify-center rounded-full 
                           bg-gradient-to-br from-amber-400 to-amber-600 
                           text-white text-[11px] font-bold shadow-sm shrink-0">
                    {{ $responsableIniciales }}
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate">
                    {{ $responsableNombre }}
                </span>
            @else
                <div class="inline-flex h-8 w-8 items-center justify-center rounded-full 
                           bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 
                           text-[11px] font-medium shrink-0">
                    ?
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400">Sin responsable</span>
            @endif
        </div>
        
        <select
            class="shrink-0 rounded-md border-gray-300 dark:border-gray-600 
                   bg-white dark:bg-gray-700 px-2 py-1 text-[11px] font-medium 
                   text-gray-700 dark:text-gray-300 
                   focus:ring-1 focus:ring-amber-500 focus:border-amber-500 
                   transition-all max-w-[130px] cursor-pointer"
            wire:change="assignResponsable({{ $tarea->id_tarea }}, parseInt($event.target.value)||null)"
            title="Cambiar responsable"
        >
            <option value="">Sin asignar</option>
            @foreach($empleados as $empleado)
                <option value="{{ $empleado->cod_empleado }}" 
                        @selected($tarea->responsable_id === $empleado->cod_empleado)>
                    {{ Str::limit($empleado->nombre_completo, 18) }}
                </option>
            @endforeach
        </select>
    </div>
</div>
