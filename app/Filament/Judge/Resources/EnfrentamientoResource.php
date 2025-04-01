<?php

namespace App\Filament\Judge\Resources;

use App\Filament\Judge\Resources\EnfrentamientoResource\Pages;
use App\Models\Enfrentamiento;
use App\Models\Equipo;
use App\Models\Llave;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Enums\IconSize;

class EnfrentamientoResource extends Resource
{
    protected static ?string $model = Enfrentamiento::class;
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'Enfrentamientos';
    protected static ?string $navigationGroup = 'Competencia';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'id';

    public static function getSlug(): string
    {
        return 'enfrentamientos';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Select::make('llave_id')
                                    ->label('Llave/Torneo')
                                    ->options(Llave::with('categoriaEvento.categoria')
                                        ->get()
                                        ->mapWithKeys(function ($llave) {
                                            $categoriaNombre = $llave->categoriaEvento->categoria->nombre ?? 'Sin categoría';
                                            return [$llave->id => "Llave #{$llave->id} - {$categoriaNombre}"];
                                        }))
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(2),
                                
                                Forms\Components\TextInput::make('ronda')
                                    ->label('Ronda')
                                    ->numeric()
                                    ->required(),
                                    
                                Forms\Components\TextInput::make('posicion')
                                    ->label('Posición')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->columns(3),
                            
                        Forms\Components\Section::make('Equipos')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Select::make('equipo1_id')
                                            ->label('Equipo 1')
                                            ->options(Equipo::all()->pluck('nombre', 'id'))
                                            ->searchable(),
                                            
                                        Forms\Components\Select::make('equipo2_id')
                                            ->label('Equipo 2')
                                            ->options(Equipo::all()->pluck('nombre', 'id'))
                                            ->searchable(),
                                    ])
                                    ->columns(2),
                            ]),
                            
                        Forms\Components\Section::make('Resultados')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('puntaje_equipo1')
                                            ->label('Puntaje Equipo 1')
                                            ->numeric()
                                            ->default(0),
                                            
                                        Forms\Components\TextInput::make('puntaje_equipo2')
                                            ->label('Puntaje Equipo 2')
                                            ->numeric()
                                            ->default(0),
                                            
                                        Forms\Components\Select::make('ganador_id')
                                            ->label('Ganador')
                                            ->options(function (callable $get) {
                                                $equipos = [];
                                                
                                                if ($get('equipo1_id')) {
                                                    $equipo1 = Equipo::find($get('equipo1_id'));
                                                    if ($equipo1) {
                                                        $equipos[$equipo1->id] = $equipo1->nombre;
                                                    }
                                                }
                                                
                                                if ($get('equipo2_id')) {
                                                    $equipo2 = Equipo::find($get('equipo2_id'));
                                                    if ($equipo2) {
                                                        $equipos[$equipo2->id] = $equipo2->nombre;
                                                    }
                                                }
                                                
                                                return $equipos;
                                            })
                                            ->searchable()
                                            ->columnSpan(2),
                                    ])
                                    ->columns(4),
                                    
                                Forms\Components\Textarea::make('observaciones')
                                    ->label('Observaciones')
                                    ->rows(3)
                                    ->columnSpan('full'),
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('llave.categoriaEvento.categoria.nombre')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('ronda')
                    ->label('Ronda')
                    ->sortable(),
                    
                TextColumn::make('equipo1.nombre')
                    ->label('Equipo 1')
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->formatStateUsing(function ($state, Enfrentamiento $record) {
                        return new HtmlString("
                            <div class='flex items-center gap-2'>
                                <span>{$state}</span>
                                " . ($record->ganador_id === $record->equipo1_id ? 
                                    "<span class='rounded-full bg-success-500 text-white px-2 text-xs py-1'>Ganador</span>" : 
                                    "") . "
                            </div>
                        ");
                    }),
                    
                TextColumn::make('puntaje_equipo1')
                    ->label('Puntos')
                    ->alignCenter()
                    ->weight(FontWeight::Bold)
                    ->size('lg'),
                    
                TextColumn::make('vs')
                    ->label('')
                    ->state('VS')
                    ->alignCenter()
                    ->weight(FontWeight::Bold)
                    ->color('gray'),
                    
                TextColumn::make('puntaje_equipo2')
                    ->label('Puntos')
                    ->alignCenter()
                    ->weight(FontWeight::Bold)
                    ->size('lg'),
                    
                TextColumn::make('equipo2.nombre')
                    ->label('Equipo 2')
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->formatStateUsing(function ($state, Enfrentamiento $record) {
                        return new HtmlString("
                            <div class='flex items-center gap-2'>
                                <span>{$state}</span>
                                " . ($record->ganador_id === $record->equipo2_id ? 
                                    "<span class='rounded-full bg-success-500 text-white px-2 text-xs py-1'>Ganador</span>" : 
                                    "") . "
                            </div>
                        ");
                    }),
                    
                Tables\Columns\IconColumn::make('estado_resultado')
                    ->label('Estado')
                    ->getStateUsing(fn (Enfrentamiento $record): bool => $record->tieneResultado())
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),
            ])
            ->filters([
                SelectFilter::make('llave_id')
                    ->label('Llave')
                    ->options(Llave::with('categoriaEvento.categoria')
                        ->get()
                        ->mapWithKeys(function ($llave) {
                            $categoriaNombre = $llave->categoriaEvento->categoria->nombre ?? 'Sin categoría';
                            return [$llave->id => "Llave #{$llave->id} - {$categoriaNombre}"];
                        })),
                        
                SelectFilter::make('ganador_id')
                    ->label('Estado')
                    ->options([
                        'pendientes' => 'Pendientes',
                        'completados' => 'Completados',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'pendientes') {
                            $query->whereNull('ganador_id')
                                ->whereNotNull('equipo1_id')
                                ->whereNotNull('equipo2_id');
                        }
                        
                        if ($data['value'] === 'completados') {
                            $query->whereNotNull('ganador_id');
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('registrarResultado')
                    ->label('Registrar Resultado')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->visible(fn (Enfrentamiento $record) => 
                        !$record->tieneResultado() && 
                        !is_null($record->equipo1_id) && 
                        !is_null($record->equipo2_id)
                    )
                    ->form([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Placeholder::make('equipo1_nombre')
                                    ->label('Equipo 1')
                                    ->content(fn (Enfrentamiento $record) => 
                                        $record->equipo1->nombre ?? 'No asignado')
                                    ->columnSpan(1),
                                    
                                Forms\Components\TextInput::make('puntaje_equipo1')
                                    ->label('Puntos')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->columnSpan(1),
                                    
                                Forms\Components\Placeholder::make('separator')
                                    ->label('')
                                    ->content('VS')
                                    ->columnSpan(1),
                                    
                                Forms\Components\TextInput::make('puntaje_equipo2')
                                    ->label('Puntos')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->columnSpan(1),
                                    
                                Forms\Components\Placeholder::make('equipo2_nombre')
                                    ->label('Equipo 2')
                                    ->content(fn (Enfrentamiento $record) => 
                                        $record->equipo2->nombre ?? 'No asignado')
                                    ->columnSpan(1),
                            ])
                            ->columns(5),
                            
                        Forms\Components\Select::make('ganador_id')
                            ->label('Seleccionar Ganador')
                            ->options(function (Enfrentamiento $record) {
                                $equipos = [];
                                
                                if ($record->equipo1_id) {
                                    $equipos[$record->equipo1_id] = $record->equipo1->nombre ?? 'Equipo 1';
                                }
                                
                                if ($record->equipo2_id) {
                                    $equipos[$record->equipo2_id] = $record->equipo2->nombre ?? 'Equipo 2';
                                }
                                
                                return $equipos;
                            })
                            ->required()
                            ->helperText('Si hay empate, seleccione manualmente el ganador'),
                            
                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->rows(2),
                    ])
                    ->action(function (Enfrentamiento $record, array $data) {
                        $record->registrarResultado(
                            $data['puntaje_equipo1'], 
                            $data['puntaje_equipo2'], 
                            $data['ganador_id']
                        );
                        
                        if (isset($data['observaciones'])) {
                            $record->update(['observaciones' => $data['observaciones']]);
                        }
                        
                        Notification::make()
                            ->title('Resultado registrado correctamente')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnfrentamientos::route('/'),
            'create' => Pages\CreateEnfrentamiento::route('/create'),
            'edit' => Pages\EditEnfrentamiento::route('/{record}/edit'),
            'view' => Pages\ViewEnfrentamiento::route('/{record}'),
        ];
    }
} 