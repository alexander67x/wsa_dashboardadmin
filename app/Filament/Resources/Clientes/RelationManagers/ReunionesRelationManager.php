<?php

namespace App\Filament\Resources\Clientes\RelationManagers;

use Filament\Forms\Components\DateTimePicker;
use App\Models\Empleado;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\Layout\Panel;

class ReunionesRelationManager extends RelationManager
{
    protected static string $relationship = 'reuniones';

    protected static ?string $title = 'Reuniones';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            DateTimePicker::make('fecha_reunion')
                ->label('Fecha de la reunión')
                ->required()
                ->seconds(false),

            TextInput::make('tipo')
                ->label('Tipo de reunión')
                ->placeholder('Kickoff, Seguimiento, Cierre, etc.'),

            TextInput::make('tema')
                ->label('Tema')
                ->maxLength(255),

            Textarea::make('descripcion')
                ->label('Descripción')
                ->rows(3)
                ->columnSpanFull(),

            Textarea::make('acuerdos')
                ->label('Acuerdos / compromisos')
                ->rows(3)
                ->columnSpanFull(),

            DateTimePicker::make('proximo_seguimiento')
                ->label('Próximo seguimiento')
                ->seconds(false),

            Select::make('responsable_interno_id')
                ->label('Responsable interno')
                ->relationship('responsableInterno', 'nombre_completo')
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('medio')
                ->label('Medio')
                ->placeholder('Presencial, Videollamada, Teléfono, etc.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Reuniones con el cliente')
            ->recordTitleAttribute('tema')
            ->columns([
                Tables\Columns\TextColumn::make('fecha_reunion')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge(),
                Tables\Columns\TextColumn::make('tema')
                    ->label('Tema')
                    ->limit(40)
                    ->wrap(),
                Tables\Columns\TextColumn::make('responsableInterno.nombre_completo')
                    ->label('Responsable interno')
                    ->formatStateUsing(fn ($state, $record) => $state ?? $record->responsable_interno)
                    ->limit(30),
                Tables\Columns\TextColumn::make('medio')
                    ->label('Medio')
                    ->limit(20),
                Tables\Columns\TextColumn::make('proximo_seguimiento')
                    ->label('Próximo seguimiento')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Panel::make([
                    Tables\Columns\TextColumn::make('descripcion')
                        ->label('Descripción')
                        ->wrap()
                        ->placeholder('Sin descripción'),
                    Tables\Columns\TextColumn::make('acuerdos')
                        ->label('Acuerdos')
                        ->wrap()
                        ->placeholder('Sin acuerdos'),
                ])
                    ->collapsed()
                    ->collapsible()
                    ->columnSpanFull(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Registrar reunión'),
            ])
            ->actions([
                EditAction::make()
                    ->label('Editar'),
                DeleteAction::make()
                    ->label('Eliminar'),
            ])
            ->emptyStateHeading('Sin reuniones registradas')
            ->emptyStateDescription('Registra las reuniones de seguimiento, kickoff o cierre que tengas con este cliente.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->syncResponsableNombre($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->syncResponsableNombre($data);
    }

    private function syncResponsableNombre(array $data): array
    {
        if (! empty($data['responsable_interno_id'])) {
            $empleado = Empleado::find($data['responsable_interno_id']);
            $data['responsable_interno'] = $empleado?->nombre_completo;
        } else {
            $data['responsable_interno'] = null;
        }

        return $data;
    }
}
