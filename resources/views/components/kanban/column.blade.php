@props([
    'id' => null,
    'nombre' => '',
    'tareas' => collect(),
    'empleados' => collect(),
    'wipLimit' => null,
    'wip-limit' => null, // Alias for kebab-case
])

@php
    // Handle both camelCase and kebab-case prop names
    $wipLimit = $wipLimit ?? $attributes->get('wip-limit') ?? null;
    
    $tareaCount = $tareas->count();
    $isWipLimitReached = $wipLimit && $tareaCount >= $wipLimit;
    $wipPercentage = $wipLimit ? ($tareaCount / $wipLimit) * 100 : 0;
@endphp

<div class="kanban-column">
    <!-- Column Header -->
    <div class="kanban-column-header">
        <div class="flex items-center justify-between mb-2">
            <h3 class="font-bold text-base text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <span class="truncate">{{ $nombre }}</span>
            </h3>
        </div>
        
        <div class="flex items-center justify-between gap-3">
            <!-- Task Count Badge -->
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full 
                             text-xs font-semibold 
                             {{ $isWipLimitReached ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200' }}">
                    {{ $tareaCount }}
                    @if($wipLimit)
                        <span class="mx-1">/</span>
                        <span>{{ $wipLimit }}</span>
                    @endif
                </span>
                
                @if($isWipLimitReached)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium 
                                 bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Límite WIP
                    </span>
                @endif
            </div>
        </div>
        
        @if($wipLimit && $tareaCount > 0)
            <!-- WIP Progress Bar -->
            <div class="mt-3 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div 
                    class="h-full rounded-full transition-all duration-300 {{ $isWipLimitReached ? 'bg-red-500' : ($wipPercentage > 80 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                    style="width: {{ min($wipPercentage, 100) }}%"
                ></div>
            </div>
        @endif
    </div>

    <!-- Column Body - Droppable Area -->
    <div
        x-data="{ 
            isOver: false,
            handleDragOver(e) {
                e.preventDefault();
                if (!isOver) {
                    isOver = true;
                }
            },
            handleDragLeave(e) {
                e.preventDefault();
                const rect = e.currentTarget.getBoundingClientRect();
                const x = e.clientX;
                const y = e.clientY;
                if (x < rect.left || x > rect.right || y < rect.top || y > rect.bottom) {
                    isOver = false;
                }
            },
            handleDrop(e) {
                e.preventDefault();
                isOver = false;
                const id = parseInt(e.dataTransfer.getData('text/plain'));
                if (id && id > 0) {
                    @if($isWipLimitReached)
                        if (!confirm('⚠️ Esta columna ha alcanzado su límite WIP ({{ $wipLimit }} tareas).\n\n¿Desea continuar de todos modos?')) {
                            return;
                        }
                    @endif
                    $wire.moveTarea(id, {{ $id }});
                }
            }
        }"
        @dragover.prevent="handleDragOver($event)"
        @dragleave.prevent="handleDragLeave($event)"
        @drop.prevent="handleDrop($event)"
        class="kanban-column-body"
        :class="{ 'drag-over': isOver }"
    >
        @forelse($tareas as $tarea)
            <x-kanban.card 
                :tarea="$tarea" 
                :empleados="$empleados" 
            />
        @empty
            <div class="empty-column-state">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
                <p class="text-sm text-gray-400 dark:text-gray-500 font-medium">
                    Sin tareas
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-600 mt-1">
                    Arrastra tareas aquí
                </p>
            </div>
        @endforelse
    </div>
</div>
