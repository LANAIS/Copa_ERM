<?php

namespace App\Filament\Competitor\Resources;

use App\Filament\Competitor\Resources\MisRobotsResource\Pages;
use App\Filament\Competitor\Resources\MisRobotsResource\RelationManagers;
use App\Models\Robot;
use App\Models\Equipo;
use App\Models\Categoria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MisRobotsResource extends Resource
{
    protected static ?string $model = Robot::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationLabel = 'Mis Robots';

    protected static ?string $navigationGroup = 'Mis Equipos';

    protected static ?string $modelLabel = 'Robot';

    protected static ?string $pluralModelLabel = 'Robots';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Información del Robot')
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('categoria_id')
                                    ->label('Categoría')
                                    ->relationship(
                                        name: 'categoria',
                                        titleAttribute: 'nombre'
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\Select::make('equipo_id')
                                    ->label('Equipo')
                                    ->relationship(
                                        name: 'equipo',
                                        titleAttribute: 'nombre',
                                        modifyQueryUsing: fn (Builder $query) => $query->where('user_id', Auth::id())
                                    )
                                    ->searchable()
                                    ->placeholder('Seleccione un equipo (opcional)')
                                    ->preload(),
                                Forms\Components\Textarea::make('descripcion')
                                    ->label('Descripción')
                                    ->required()
                                    ->maxLength(1000)
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('especificaciones')
                                    ->label('Especificaciones técnicas')
                                    ->maxLength(1000)
                                    ->columnSpanFull(),
                                Forms\Components\Hidden::make('categoria')
                                    ->dehydrateStateUsing(fn ($state, callable $get) => 'categoria_' . $get('categoria_id')),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Imagen')
                            ->schema([
                                Forms\Components\FileUpload::make('imagen')
                                    ->label('Imagen del Robot')
                                    ->image()
                                    ->disk('public')
                                    ->directory('robots')
                                    ->visibility('public')
                                    ->required(fn (?Robot $record) => $record === null)
                                    ->imageEditor()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('imagen')
                    ->label('Imagen')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('equipo.nombre')
                    ->label('Equipo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoria_id')
                    ->label('Categoría')
                    ->relationship('categoria', 'nombre'),
                Tables\Filters\SelectFilter::make('equipo')
                    ->relationship('equipo', 'nombre', fn (Builder $query) => $query->where('user_id', Auth::id())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Robot $record) {
                        // Eliminar la imagen asociada
                        if ($record->imagen && Storage::disk('public')->exists($record->imagen)) {
                            Storage::disk('public')->delete($record->imagen);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->imagen && Storage::disk('public')->exists($record->imagen)) {
                                    Storage::disk('public')->delete($record->imagen);
                                }
                            }
                        }),
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
            'index' => Pages\ListMisRobots::route('/'),
            'create' => Pages\CreateMisRobots::route('/create'),
            'edit' => Pages\EditMisRobots::route('/{record}/edit'),
        ];
    }

    // Solo mostrar robots del usuario autenticado o de sus equipos
    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $equiposIds = Equipo::where('user_id', $user->id)->pluck('id');
        
        return parent::getEloquentQuery()
            ->where(function($query) use ($user, $equiposIds) {
                $query->where('user_id', $user->id)
                      ->orWhereIn('equipo_id', $equiposIds);
            });
    }
}
