<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookmakerResource\Pages;
use App\Models\Bookmaker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BookmakerResource extends Resource
{
    protected static ?string $model = Bookmaker::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Bookmakers';
    protected static ?string $modelLabel = 'Bookmaker';
    protected static ?string $pluralModelLabel = 'Bookmakers';
    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: 1xBet'),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Ex: 1xbet')
                            ->helperText('Identifiant unique (minuscules, tirets)'),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(500),
                    ])->columns(2),

                Forms\Components\Section::make('Design')
                    ->schema([
                        Forms\Components\ColorPicker::make('primary_color')
                            ->label('Couleur primaire')
                            ->default('#FF0000'),
                        Forms\Components\ColorPicker::make('secondary_color')
                            ->label('Couleur secondaire'),
                        Forms\Components\TextInput::make('logo_url')
                            ->label('URL Logo')
                            ->url()
                            ->maxLength(500),
                    ])->columns(3),

                Forms\Components\Section::make('Liens')
                    ->schema([
                        Forms\Components\TextInput::make('affiliate_link')
                            ->label('Lien affilié')
                            ->url()
                            ->maxLength(500)
                            ->helperText('URL avec ID affilié'),
                        Forms\Components\TextInput::make('download_link')
                            ->label('Lien téléchargement')
                            ->url()
                            ->maxLength(500)
                            ->helperText('URL APK ou store'),
                    ])->columns(2),

                Forms\Components\Section::make('Statut')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0)
                            ->helperText('Plus petit = affiché en premier'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('primary_color')
                    ->label('Couleur')
                    ->badge()
                    ->color(fn ($record) => $record->primary_color),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable(),
                Tables\Columns\TextColumn::make('clicks_count')
                    ->label('Clics')
                    ->counts('clicks')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Actif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
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
            'index' => Pages\ListBookmakers::route('/'),
            'create' => Pages\CreateBookmaker::route('/create'),
            'edit' => Pages\EditBookmaker::route('/{record}/edit'),
        ];
    }
}
