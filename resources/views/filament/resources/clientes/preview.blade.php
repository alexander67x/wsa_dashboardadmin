@php
    use App\Filament\Resources\Clientes\ClienteResource;

    $archivos = $cliente->archivos ?? collect();

    $formatBytes = static function (?int $bytes): string {
        if (! $bytes) {
            return '—';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = (int) floor(log($bytes, 1024));
        $pow = min($pow, count($units) - 1);

        $value = $bytes / (1024 ** $pow);

        return number_format($value, $value >= 10 ? 0 : 1).' '.$units[$pow];
    };
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
        <div>
            <p class="text-lg font-semibold text-gray-900">{{ $cliente->nombre_cliente }}</p>
            <p class="text-sm text-gray-500">{{ $cliente->industria ?? 'Industria no especificada' }}</p>
        </div>

        <div class="flex gap-3">
            <span @class([
                'inline-flex items-center rounded-full px-3 py-1 text-xs font-medium',
                $cliente->activo
                    ? 'bg-green-100 text-green-800'
                    : 'bg-gray-200 text-gray-600',
            ])>
                {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
            </span>
            <a
                href="{{ ClienteResource::getUrl('edit', ['record' => $cliente]) }}"
                class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
            >
                Editar cliente
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700">Información general</h3>
            <dl class="mt-4 space-y-3 text-sm text-gray-600">
                <div class="flex justify-between">
                    <dt class="font-medium text-gray-800">Contacto principal</dt>
                    <dd>{{ $cliente->contacto_principal ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-medium text-gray-800">Correo</dt>
                    <dd>{{ $cliente->email ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-medium text-gray-800">Teléfono</dt>
                    <dd>{{ $cliente->telefono ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-medium text-gray-800">Dirección</dt>
                    <dd class="text-right">{{ $cliente->direccion ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700">Actividad reciente</h3>
            <dl class="mt-4 space-y-3 text-sm text-gray-600">
                <div class="flex justify-between">
                    <dt class="font-medium text-gray-800">Proyectos activos</dt>
                    <dd>{{ $cliente->proyectos_count ?? '0' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-medium text-gray-800">Reuniones registradas</dt>
                    <dd>{{ $cliente->reuniones_count ?? '0' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-medium text-gray-800">Creado</dt>
                    <dd>{{ optional($cliente->created_at)?->format('d/m/Y H:i') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="font-medium text-gray-800">Actualizado</dt>
                    <dd>{{ optional($cliente->updated_at)?->format('d/m/Y H:i') ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Documentos recientes</h3>
            <p class="text-xs text-gray-500">Se muestran los últimos {{ $archivos->count() }} archivos</p>
        </div>

        @if($archivos->isEmpty())
            <p class="mt-4 text-sm text-gray-500">Este cliente aún no tiene documentos.</p>
        @else
            <ul class="mt-4 divide-y divide-gray-200">
                @foreach($archivos as $archivo)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $archivo->nombre_original }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $archivo->tipo_mime ?? 'Archivo' }}
                                · {{ $formatBytes($archivo->tamano_bytes) }}
                            </p>
                        </div>
                        <a
                            href="{{ $archivo->url }}"
                            target="_blank"
                            class="text-sm font-medium text-primary-600 hover:text-primary-700"
                        >
                            Ver
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
