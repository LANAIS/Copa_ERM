<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipoResource\Pages;
use App\Filament\Resources\EquipoResource\RelationManagers;
use App\Models\Equipo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class EquipoResource extends Resource
{
    protected static ?string $model = Equipo::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'Equipos';

    protected static ?string $navigationGroup = 'Torneos';

    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id()),
                
                Forms\Components\Section::make('Información del Equipo')
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre del Equipo')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción')
                            ->rows(4)
                            ->placeholder('Describe tu equipo, su historia, misión, y metas...'),
                    ]),
                    
                Forms\Components\Section::make('Contacto y Redes Sociales')
                    ->schema([
                        Forms\Components\TextInput::make('sitio_web')
                            ->label('Sitio Web')
                            ->url()
                            ->prefixIcon('heroicon-o-globe-alt')
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->prefixIcon('heroicon-o-envelope')
                            ->maxLength(255),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('instagram')
                                    ->label('Instagram')
                                    ->prefixIcon('heroicon-o-camera')
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('facebook')
                                    ->label('Facebook')
                                    ->prefixIcon('heroicon-o-share')
                                    ->maxLength(255),
                                    
                                Forms\Components\TextInput::make('youtube')
                                    ->label('YouTube')
                                    ->prefixIcon('heroicon-o-play')
                                    ->maxLength(255),
                                    
                                Forms\Components\TextInput::make('linkedin')
                                    ->label('LinkedIn')
                                    ->prefixIcon('heroicon-o-briefcase')
                                    ->maxLength(255),
                            ]),
                    ]),
                    
                Forms\Components\Section::make('Imágenes')
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->label('Logo del Equipo')
                            ->image()
                            ->directory('equipos/logos')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->helperText('Tamaño máximo: 2MB. Formatos: jpg, png, svg, webp'),
                            
                        Forms\Components\FileUpload::make('banner')
                            ->label('Banner del Equipo')
                            ->image()
                            ->directory('equipos/banners')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->helperText('Tamaño máximo: 5MB. Formatos: jpg, png, webp. Dimensión recomendada: 1200x300 pixeles'),
                    ]),
                    
                Forms\Components\Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Solo mostrar equipos del usuario actual
                return $query->where('user_id', Auth::id());
            })
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular(),
                    
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\ToggleColumn::make('activo')
                    ->label('Activo'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\MiembrosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEquipos::route('/'),
            'create' => Pages\CreateEquipo::route('/create'),
            'edit' => Pages\EditEquipo::route('/{record}/edit'),
        ];
    }
}
