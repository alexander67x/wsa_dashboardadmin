@php
    $permissions = $role?->permissions ?? collect();
@endphp

<div class="space-y-2">
    @if ($role)
        <p class="text-sm text-gray-600 dark:text-gray-300">
            Privilegios de <strong>{{ $role->nombre }}</strong>:
        </p>
        @if ($permissions->isEmpty())
            <p class="text-sm text-gray-500">
                Este rol aún no tiene privilegios asignados.
            </p>
        @else
            <div class="flex flex-wrap gap-2">
                @foreach ($permissions as $permission)
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-primary-50 text-primary-800 dark:bg-primary-900/40 dark:text-primary-200">
                        {{ $permission->descripcion ?? $permission->codigo }}
                    </span>
                @endforeach
            </div>
        @endif
    @else
        <p class="text-sm text-gray-500">
            Selecciona un rol para visualizar sus privilegios.
        </p>
    @endif
</div>
