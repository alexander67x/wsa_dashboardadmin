<x-filament::page>
    @push('styles')
        <style>
            /* ============================================
               KANBAN BOARD STYLES
               ============================================ */
            
            .kanban-wrapper {
                background: linear-gradient(to bottom right, #f9fafb, #f3f4f6);
                border-radius: 0.75rem;
                padding: 1.5rem;
                min-height: calc(100vh - 250px);
            }

            .dark .kanban-wrapper {
                background: linear-gradient(to bottom right, #111827, #1f2937);
            }

            .kanban-container {
                display: flex;
                gap: 1rem;
                overflow-x: auto;
                padding-bottom: 1rem;
                min-height: 600px;
                align-items: flex-start;
                scrollbar-width: thin;
                scrollbar-color: rgba(156, 163, 175, 0.4) transparent;
                padding-inline-end: 1rem;
            }
            
            .kanban-container::-webkit-scrollbar {
                height: 8px;
            }
            
            .kanban-container::-webkit-scrollbar-track {
                background: transparent;
            }
            
            .kanban-container::-webkit-scrollbar-thumb {
                background-color: #d1d5db;
                border-radius: 9999px;
            }

            .dark .kanban-container::-webkit-scrollbar-thumb {
                background-color: #4b5563;
            }
            
            .kanban-container::-webkit-scrollbar-thumb:hover {
                background-color: #9ca3af;
            }

            .dark .kanban-container::-webkit-scrollbar-thumb:hover {
                background-color: #6b7280;
            }

            /* Kanban Column Styles */
            .kanban-column {
                background-color: white;
                border-radius: 0.75rem;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                display: flex;
                flex-direction: column;
                min-width: 320px;
                width: 320px;
                max-height: calc(100vh - 200px);
                transition: all 0.2s;
                border: 1px solid #e5e7eb;
            }

            .dark .kanban-column {
                background-color: #1f2937;
                border-color: #374151;
            }

            .kanban-column:hover {
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                border-color: #fcd34d;
            }

            .dark .kanban-column:hover {
                border-color: #92400e;
            }

            .kanban-column-header {
                padding: 1.25rem 1.5rem;
                border-bottom: 1px solid #e5e7eb;
                background: linear-gradient(to right, #f9fafb, white);
                border-top-left-radius: 0.75rem;
                border-top-right-radius: 0.75rem;
                position: sticky;
                top: 0;
                z-index: 20;
                backdrop-filter: blur(4px);
            }

            .dark .kanban-column-header {
                background: linear-gradient(to right, #1f2937, #111827);
                border-bottom-color: #374151;
            }

            .kanban-column-body {
                flex: 1;
                padding: 0.75rem 1rem;
                overflow-y: auto;
                overflow-x: hidden;
                min-height: 200px;
                transition: all 0.3s;
                scrollbar-width: thin;
                scrollbar-color: rgba(156, 163, 175, 0.3) transparent;
            }

            .kanban-column-body::-webkit-scrollbar {
                width: 6px;
            }

            .kanban-column-body::-webkit-scrollbar-track {
                background: transparent;
            }

            .kanban-column-body::-webkit-scrollbar-thumb {
                background-color: #d1d5db;
                border-radius: 9999px;
            }

            .dark .kanban-column-body::-webkit-scrollbar-thumb {
                background-color: #4b5563;
            }

            .kanban-column-body.drag-over {
                background-color: #fffbeb;
                border: 2px dashed #fbbf24;
                border-radius: 0.5rem;
            }

            .dark .kanban-column-body.drag-over {
                background-color: rgba(146, 64, 14, 0.2);
                border-color: #d97706;
            }

            /* Kanban Card Styles */
            .kanban-card {
                background-color: white;
                border-radius: 0.5rem;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                padding: 1rem;
                margin-bottom: 0.75rem;
                border-left: 4px solid #d1d5db;
                transition: all 0.2s;
                cursor: grab;
                position: relative;
            }

            .dark .kanban-card {
                background-color: #1f2937;
                border-left-color: #4b5563;
            }

            .kanban-card:hover {
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                transform: translateY(-2px);
                border-left-color: #fbbf24;
            }

            .dark .kanban-card:hover {
                border-left-color: #d97706;
            }

            .kanban-card.dragging {
                opacity: 0.5;
                transform: rotate(2deg) scale(0.95);
                cursor: grabbing;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                z-index: 50;
            }

            .kanban-card.priority-alta {
                border-left-color: #ef4444;
            }

            .kanban-card.priority-media {
                border-left-color: #f59e0b;
            }

            .kanban-card.priority-baja {
                border-left-color: #10b981;
            }

            .empty-column-state {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 3rem 1rem;
                text-align: center;
                border: 2px dashed #d1d5db;
                border-radius: 0.5rem;
                background-color: rgba(249, 250, 251, 0.5);
            }

            .dark .empty-column-state {
                border-color: #4b5563;
                background-color: rgba(31, 41, 55, 0.5);
            }

            /* Priority Badges */
            .priority-badge {
                display: inline-flex;
                align-items: center;
                padding: 0.25rem 0.625rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }

            .priority-alta-badge {
                background-color: #fee2e2;
                color: #991b1b;
            }

            .dark .priority-alta-badge {
                background-color: rgba(153, 27, 27, 0.3);
                color: #fca5a5;
            }

            .priority-media-badge {
                background-color: #fef3c7;
                color: #92400e;
            }

            .dark .priority-media-badge {
                background-color: rgba(146, 64, 14, 0.3);
                color: #fcd34d;
            }

            .priority-baja-badge {
                background-color: #d1fae5;
                color: #065f46;
            }

            .dark .priority-baja-badge {
                background-color: rgba(6, 95, 70, 0.3);
                color: #6ee7b7;
            }

            /* Empty States */
            .empty-state {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 4rem 2rem;
                text-align: center;
                background-color: white;
                border-radius: 0.75rem;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                border: 1px solid #e5e7eb;
            }

            .dark .empty-state {
                background-color: #1f2937;
                border-color: #374151;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .kanban-column {
                    min-width: 280px;
                    width: 280px;
                }
                
                .kanban-container {
                    gap: 0.75rem;
                }
            }
            
            /* Icon sizing overrides (scoped to this page) */
            /* General cap to avoid oversized SVGs regardless of utility classes */
            .planificacion-page svg { max-width: 1.5rem; max-height: 1.5rem; height: auto; }
            /* Increased specificity to beat global rules using duplicated class selectors */
            .planificacion-page svg.w-6.w-6, .planificacion-page svg.h-6.h-6 { width: 1.5rem !important; height: 1.5rem !important; }
            .planificacion-page svg.w-5.w-5, .planificacion-page svg.h-5.h-5 { width: 1.25rem !important; height: 1.25rem !important; }
            .planificacion-page svg.w-4.w-4, .planificacion-page svg.h-4.h-4 { width: 1rem !important; height: 1rem !important; }
            .planificacion-page svg.inline { display: inline-block; vertical-align: middle; }
            /* Hard cap for common containers regardless of classes */
            .planificacion-page label svg,
            .planificacion-page button svg,
            .planificacion-page .kanban-column-header svg {
                width: 1.25rem !important;
                height: 1.25rem !important;
            }
        </style>
    @endpush
    
    <div class="planificacion-page space-y-6">
        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <!-- Project Selector -->
                <div class="mb-6">
                    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Seleccionar Proyecto
                    </label>
                    <select 
                        wire:model.live="codProy" 
                        class="w-full md:max-w-md rounded-lg border-gray-300 dark:border-gray-600 
                               bg-white dark:bg-gray-700 px-4 py-3 text-sm font-medium 
                               text-gray-900 dark:text-gray-100 
                               focus:ring-2 focus:ring-amber-500 focus:border-amber-500 
                               transition-all shadow-sm"
                    >
                        <option value="">-- Selecciona un proyecto --</option>
                        @foreach($this->proyectos as $proyecto)
                            <option value="{{ $proyecto->cod_proy }}">
                                {{ $proyecto->cod_proy }} — {{ $proyecto->nombre_ubicacion }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($codProy)
                    <!-- Quick Task Creation Form -->
                    <div class="bg-gradient-to-r from-amber-50 to-amber-100/50 dark:from-amber-900/20 dark:to-amber-800/20 
                                rounded-lg p-4 border border-amber-200 dark:border-amber-800">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                Crear Nueva Tarea
                            </h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Título de la Tarea
                                </label>
                                <input 
                                    type="text" 
                                    placeholder="Ej: Implementar funcionalidad X..." 
                                    wire:model.defer="nuevoTitulo" 
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 
                                           bg-white dark:bg-gray-700 px-4 py-2.5 text-sm 
                                           text-gray-900 dark:text-gray-100 
                                           focus:ring-2 focus:ring-amber-500 focus:border-amber-500 
                                           transition-all placeholder:text-gray-400"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Responsable
                                </label>
                                <select 
                                    wire:model.defer="nuevoResponsable" 
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 
                                           bg-white dark:bg-gray-700 px-4 py-2.5 text-sm font-medium 
                                           text-gray-900 dark:text-gray-100 
                                           focus:ring-2 focus:ring-amber-500 focus:border-amber-500 
                                           transition-all"
                                >
                                    <option value="">-- Selecciona --</option>
                                    @foreach($this->empleados as $empleado)
                                        <option value="{{ $empleado->cod_empleado }}">
                                            {{ $empleado->nombre_completo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4 flex justify-end">
                            <x-filament::button
                                color="warning"
                                size="md"
                                wire:click="createTarea"
                                :disabled="!$codProy || !$nuevoTitulo || !$nuevoResponsable"
                                class="gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Crear Tarea
                            </x-filament::button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Kanban Board Section -->
        @if($codProy && $board)
            <div class="kanban-wrapper">
                <div class="kanban-container" x-data="{ draggedId: null }">
                    @foreach($columns as $columnId => $column)
                        <x-kanban.column 
                            :id="$columnId" 
                            :nombre="$column['nombre']" 
                            :tareas="collect($tareasByColumn[$columnId] ?? [])" 
                            :empleados="$this->empleados" 
                            :wip-limit="$column['wip_limit'] ?? null"
                        />
                    @endforeach
                </div>
            </div>
        @elseif($codProy && !$board)
            <div class="empty-state">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span>Inicializando Tablero Kanban</span>
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Estamos preparando el tablero para este proyecto...
                </p>
            </div>
        @else
            <div class="empty-state">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                    <span>Selecciona un Proyecto</span>
                </h3>
                <p class="text-gray-600 dark:text-gray-400 max-w-md">
                    Para comenzar, selecciona un proyecto del menú superior para visualizar y gestionar sus tareas en el tablero Kanban.
                </p>
            </div>
        @endif
    </div>
</x-filament::page>
