<?php

namespace App\Filament\Resources\Materiales\Schemas;

use App\Models\Almacen;
use App\Models\Material;
use App\Models\MaterialGrupo;
use App\Models\MaterialSubgrupo;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class MaterialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('codigo_producto')
                    ->label('Código del Producto')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('Ej: MAT-001')
                    ->columnSpan(1),

                TextInput::make('nombre_producto')
                    ->label('Nombre del Producto')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                Select::make('id_grupo')
                    ->label('Grupo')
                    ->options(fn () => MaterialGrupo::orderBy('nombre')->pluck('nombre', 'id_grupo')->toArray())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->dehydrated(false)
                    ->createOptionForm([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required(),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        $lastCode = MaterialGrupo::query()
                            ->orderByDesc('id_grupo')
                            ->value('codigo_grupo');

                        $nextNumber = 1;
                        if ($lastCode && preg_match('/GRP-(\d+)/', $lastCode, $matches)) {
                            $nextNumber = ((int) $matches[1]) + 1;
                        }

                        $codigo = 'GRP-'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);

                        $grupo = MaterialGrupo::create([
                            'codigo_grupo' => $codigo,
                            'nombre' => $data['nombre'],
                        ]);

                        return $grupo->getKey();
                    })
                    ->afterStateUpdated(function (Set $set) {
                        $set('id_subgrupo', null);
                    })
                    ->afterStateHydrated(function (Select $component, $state, ?Material $record): void {
                        if (! $record?->subgrupo?->id_grupo) {
                            return;
                        }

                        $component->state((string) $record->subgrupo->id_grupo);
                    })
                    ->columnSpan(1),

                Select::make('id_subgrupo')
                    ->label('Subgrupo')
                    ->options(function (Get $get) {
                        $grupoId = $get('id_grupo');

                        if (! $grupoId) {
                            return [];
                        }

                        return MaterialSubgrupo::query()
                            ->where('id_grupo', $grupoId)
                            ->orderBy('nombre')
                            ->pluck('nombre', 'id_subgrupo')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn (Get $get) => ! $get('id_grupo'))
                    ->createOptionForm([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required(),
                    ])
                    ->createOptionUsing(function (Select $component, array $data): int {
                        $parentState = $component->getContainer()->getRawState();
                        $grupoId = $parentState['id_grupo'] ?? null;

                        if (! $grupoId) {
                            throw new \Exception('Debe seleccionar un grupo antes de crear un subgrupo.');
                        }

                        $lastCode = MaterialSubgrupo::query()
                            ->orderByDesc('id_subgrupo')
                            ->value('codigo_subgrupo');

                        $nextNumber = 1;
                        if ($lastCode && preg_match('/SUB-(\d+)/', $lastCode, $matches)) {
                            $nextNumber = ((int) $matches[1]) + 1;
                        }

                        $codigo = 'SUB-'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);

                        $subgrupo = MaterialSubgrupo::create([
                            'id_grupo' => $grupoId,
                            'codigo_subgrupo' => $codigo,
                            'nombre' => $data['nombre'],
                        ]);

                        return $subgrupo->getKey();
                    })
                    ->columnSpan(1),

                TextInput::make('unidad_medida')
                    ->label('Unidad de Medida')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ej: kg, m, unidades, cajas')
                    ->columnSpan(1),

                TextInput::make('costo_unitario_promedio_bs')
                    ->label('Costo Unitario Promedio (Bs.)')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('Bs.')
                    ->default(0)
                    ->columnSpan(1),

                TextInput::make('equivalencia')
                    ->label('Equivalencia')
                    ->numeric()
                    ->step(0.01)
                    ->helperText('Ej: 1 caja = 12 unidades')
                    ->columnSpan(1),

                TextInput::make('unidad_equivalencia')
                    ->label('Unidad de Equivalencia')
                    ->maxLength(255)
                    ->placeholder('Ej: caja, paquete')
                    ->columnSpan(1),

                TextInput::make('stock_minimo')
                    ->label('Stock Mínimo')
                    ->numeric()
                    ->step(0.01)
                    ->default(0)
                    ->required()
                    ->columnSpan(1),

                TextInput::make('stock_maximo')
                    ->label('Stock Máximo')
                    ->numeric()
                    ->step(0.01)
                    ->columnSpan(1),

                Select::make('criticidad')
                    ->label('Criticidad')
                    ->options([
                        'critico' => 'Crítico',
                        'no_critico' => 'No Crítico',
                    ])
                    ->default('no_critico')
                    ->required()
                    ->columnSpan(1),

                Select::make('almacenes')
                    ->label('Almacenes')
                    ->relationship('almacenes', 'nombre', fn ($query) => $query->where('activo', true))
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->codigo_almacen} - {$record->nombre} ({$record->tipo})")
                    ->helperText('Seleccione los almacenes donde está disponible este material (puede ser almacén central o subalmacenes)')
                    ->columnSpanFull(),

                Toggle::make('activo')
                    ->label('Activo')
                    ->default(true)
                    ->required()
                    ->columnSpan(1),
            ]);
    }
}
