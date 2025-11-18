<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Selector de solicitud --}}
        <x-filament::section>
            <x-slot name="heading">Seleccionar solicitud</x-slot>

            <x-filament::input.wrapper>
                <x-filament::input.select
                    wire:model="solicitudId"
                >
                    <option value="">-- Elige una solicitud --</option>
                    @foreach ($this->solicitudes as $s)
                        <option value="{{ $s->id_solicitud }}">
                            {{ $s->numero_solicitud }}
                        </option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </x-filament::section>

        @if ($solicitud)
            <x-filament::section>
                <x-slot name="heading">
                    {{ $solicitud->numero_solicitud }} ({{ $solicitud->estado }})
                </x-slot>

                {{-- Historial de eventos --}}
                <div class="space-y-3">
                    <h3 class="font-semibold text-sm">Historial</h3>
                    <ul class="space-y-2 text-sm">
                        @foreach ($solicitud->historial as $evento)
                            <li>
                                <span class="font-medium">
                                    {{ $evento->fecha_evento?->format('d/m/Y H:i') ?? '-' }}
                                </span>
                                — {{ $evento->tipo_evento }}
                                @if ($evento->empleado?->nombre_completo)
                                    ({{ $evento->empleado->nombre_completo }})
                                @elseif ($evento->usuario?->name)
                                    ({{ $evento->usuario->name }})
                                @endif
                                @if ($evento->descripcion)
                                    · {{ $evento->descripcion }}
                                @endif
                                @if ($evento->observaciones)
                                    · {{ $evento->observaciones }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Entregas --}}
                <div class="mt-6 space-y-3">
                    <h3 class="font-semibold text-sm">Entregas</h3>
                    <ul class="space-y-2 text-sm">
                        @forelse ($solicitud->deliveries as $delivery)
                            <li>
                                {{ $delivery->fecha_entrega?->format('d/m/Y H:i') ?? '-' }}
                                — {{ $delivery->estado ?? 'sin estado' }}
                                · {{ $delivery->numero_entrega ?? 'Entrega' }}
                                @if ($delivery->cantidad_entregada)
                                    · Cant: {{ $delivery->cantidad_entregada }}
                                @endif
                                @if ($delivery->observaciones)
                                    · {{ $delivery->observaciones }}
                                @endif
                            </li>
                        @empty
                            <li>No hay entregas registradas.</li>
                        @endforelse
                    </ul>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>

