<x-filament::page>
    @push('styles')
        <style>
            .kb-board{display:grid;grid-template-columns:repeat(1,minmax(0,1fr));gap:20px}
            @media(min-width:1024px){.kb-board{grid-template-columns:repeat(3,minmax(0,1fr))}}
            @media(min-width:1280px){.kb-board{grid-template-columns:repeat(4,minmax(0,1fr))}}
            .kb-column{background:#f9fafb;border:1px solid rgba(0,0,0,.08);border-radius:10px}
            .kb-column-header{position:sticky;top:0;z-index:10;background:rgba(249,250,251,.9);border-bottom:1px solid rgba(0,0,0,.08);padding:10px 12px;border-top-left-radius:10px;border-top-right-radius:10px}
            .kb-column-body{padding:12px;min-height:16rem;max-height:65vh;overflow:auto;transition:background-color .2s ease}
            .kb-column-body.kb-over{background:rgba(245,158,11,.08)}
            .kb-card{background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:8px;padding:12px;box-shadow:0 1px 2px rgba(0,0,0,.05)}
            .kb-card:hover{box-shadow:0 2px 6px rgba(0,0,0,.08)}
            .kb-badge{display:inline-flex;align-items:center;gap:6px;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600}
            .kb-badge.gray{background:#e5e7eb;color:#374151}
            .kb-badge.warn{background:#FEF3C7;color:#92400E}
            .kb-badge.ok{background:#DCFCE7;color:#166534}
        </style>
    @endpush
    <div class="space-y-6">
        <!-- Header -->
        <x-filament::section>
            <div class="flex flex-col md:flex-row md:items-end gap-3 md:gap-4">
                <div class="md:w-96">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Proyecto</label>
                    <select wire:model.live="codProy" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        <option value="">Selecciona un proyecto</option>
                        @foreach($this->proyectos as $p)
                            <option value="{{ $p->cod_proy }}">{{ $p->cod_proy }} — {{ $p->nombre_ubicacion }}</option>
                        @endforeach
                    </select>
                </div>

                @if($codProy)
                    <div class="flex-1"></div>
                    <div class="flex items-end gap-2 md:gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Título</label>
                            <input type="text" placeholder="Nueva tarea" wire:model.defer="nuevoTitulo" class="w-56 md:w-72 rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Responsable</label>
                            <select wire:model.defer="nuevoResponsable" class="w-44 md:w-56 rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <option value="">Selecciona responsable</option>
                                @foreach($this->empleados as $e)
                                    <option value="{{ $e->cod_empleado }}">{{ $e->nombre_completo }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-filament::button
                            color="warning"
                            size="sm"
                            wire:click="createTarea"
                            :disabled="!$codProy || !$nuevoTitulo || !$nuevoResponsable"
                        >
                            Crear tarea
                        </x-filament::button>
                    </div>
                @endif
            </div>
        </x-filament::section>

        @if($codProy && $board)
            <!-- Board estilo Trello con Drag & Drop -->
            <div class="kb-board items-start">
                @foreach($columns as $colId => $col)
                    <x-kanban.column :id="$colId" :nombre="$col['nombre']" :tareas="collect($tareasByColumn[$colId] ?? [])" :empleados="$this->empleados" />
                @endforeach
                
            </div>
        @endif
    </div>
</x-filament::page>
