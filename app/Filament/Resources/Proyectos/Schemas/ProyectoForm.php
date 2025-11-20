<?php

namespace App\Filament\Resources\Proyectos\Schemas;

use App\Filament\Components\MapPicker;
use App\Models\Cliente;
use App\Models\Empleado;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ProyectoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('cod_proy')
                    ->label('Código del Proyecto')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('Ej: PROY-2024-001'),

                Select::make('cod_cliente')
                    ->label('Cliente')
                    ->relationship('cliente', 'nombre_cliente')
                    ->searchable()
                    ->preload()
                    ->required(),

                // Campos para crear la ubicación integrada en proyectos
                TextInput::make('nombre_ubicacion')
                    ->label('Nombre de la Ubicación')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ej: Obra Residencial Miraflores'),

                Textarea::make('direccion')
                    ->label('Dirección de la Ubicación')
                    ->required()
                    ->rows(2)
                    ->placeholder('Dirección completa de la obra'),

                TextInput::make('ciudad')
                    ->label('Ciudad')
                    ->required()
                    ->maxLength(255)
                    ->default('La Paz'),

                TextInput::make('pais')
                    ->label('País')
                    ->required()
                    ->maxLength(255)
                    ->default('Bolivia'),

                // Mapa y coordenadas
                MapPicker::make('coordenadas')
                    ->label('Ubicación en el Mapa')
                    ->columnSpanFull(),

                // Campos ocultos para latitud y longitud (se llenan automáticamente desde el mapa)
                TextInput::make('latitud')
                    ->label('Latitud')
                    ->numeric()
                    ->step(0.0000001)
                    ->hidden()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $coordenadas = $get('coordenadas');
                        if ($coordenadas && is_array($coordenadas)) {
                            $coordenadas['latitude'] = $state;
                            $set('coordenadas', $coordenadas);
                        }
                    }),

                TextInput::make('longitud')
                    ->label('Longitud')
                    ->numeric()
                    ->step(0.0000001)
                    ->hidden()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $coordenadas = $get('coordenadas');
                        if ($coordenadas && is_array($coordenadas)) {
                            $coordenadas['longitude'] = $state;
                            $set('coordenadas', $coordenadas);
                        }
                    }),

                // Campo para asignar empleados al proyecto
                Select::make('empleados')
                    ->label('Empleados Asignados')
                    ->multiple()
                    // Usar opciones directas para evitar que Filament haga sync automático
                    ->options(fn () => Empleado::orderBy('nombre_completo')->pluck('nombre_completo', 'cod_empleado')->toArray())
                    ->searchable()
                    ->columnSpanFull(),

                Select::make('responsable_proyecto')
                    ->label('Responsable del Proyecto')
                    ->relationship('responsable', 'nombre_completo')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->extraAttributes(['class' => 'dropup-select']),

                Select::make('supervisor_obra')
                    ->label('Supervisor de Obra')
                    ->relationship('supervisor', 'nombre_completo')
                    ->searchable()
                    ->preload()
                    ->extraAttributes(['class' => 'dropup-select']),

                DatePicker::make('fecha_inicio')
                    ->label('Fecha de Inicio')
                    ->required()
                    ->default(now()),

                DatePicker::make('fecha_fin_estimada')
                    ->label('Fecha Fin Estimada')
                    ->after('fecha_inicio'),

                DatePicker::make('fecha_fin_real')
                    ->label('Fecha Fin Real')
                    ->after('fecha_inicio'),

                Select::make('estado')
                    ->label('Estado del Proyecto')
                    ->options([
                        'activo' => 'Activo',
                        'completado' => 'Completado',
                        'cancelado' => 'Cancelado',
                        'pausado' => 'Pausado',
                    ])
                    ->default('activo')
                    ->required(),

                Textarea::make('descripcion')
                    ->label('Descripción del Proyecto')
                    ->columnSpanFull()
                    ->rows(3),

                FileUpload::make('cotizaciones')
                    ->label('Cotizaciones del proyecto')
                    ->multiple()
                    ->disk('public')
                    ->directory('proyectos/cotizaciones')
                    ->preserveFilenames()
                    ->openable()
                    ->downloadable()
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->helperText('Sube archivos de cotizaciones, se versionarán automáticamente (V1, V2, etc.).'),

                // presupuesto_inicial eliminado: gestionado fuera o no aplicable

                TextInput::make('avance_financiero')
                    ->label('Avance Financiero')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01),

                TextInput::make('gasto_real')
                    ->label('Gasto Real')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01),

                TextInput::make('rentabilidad')
                    ->label('Rentabilidad (%)')
                    ->numeric()
                    ->suffix('%')
                    ->step(0.01),
            ]);
    }
}
