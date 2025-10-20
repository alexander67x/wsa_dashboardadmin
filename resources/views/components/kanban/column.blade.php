@props([
    'id' => null,
    'nombre' => '',
    'tareas' => collect(),
    'empleados' => collect(),
])

<div class="kb-column">
    <div class="kb-column-header">
        <div class="flex items-center justify-between gap-2">
            <div class="font-medium text-[13px] text-gray-800 dark:text-gray-100">{{ $nombre }}</div>
            <div class="flex items-center gap-2">
                <span class="kb-badge gray">{{ $tareas->count() }}</span>
            </div>
        </div>
    </div>

    <div
        x-data="{ isOver: false }"
        @dragover.prevent="isOver = true"
        @dragleave.prevent="isOver = false"
        @drop.prevent="
            isOver = false;
            const id = parseInt(event.dataTransfer.getData('text/plain'));
            if (id) { $wire.moveTarea(id, {{ $id }}); }
        "
        class="kb-column-body space-y-3"
        :class="isOver ? 'kb-over' : ''"
    >
        @forelse($tareas as $t)
            <x-kanban.card :tarea="$t" :empleados="$empleados" />
        @empty
            <div class="text-sm text-gray-500 border border-dashed rounded-md px-3 py-6 text-center">Sin tarjetas aún</div>
        @endforelse
    </div>
</div>


