<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppConfigResource\Pages;
use App\Models\AppConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AppConfigResource extends Resource
{
    protected static ?string $model = AppConfig::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Configuration App';
    protected static ?string $modelLabel = 'Configuration';
    protected static ?string $pluralModelLabel = 'Configurations';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label('Clé')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Ex: app_name, primary_color, welcome_message'),
                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        'string' => 'Texte',
                        'integer' => 'Nombre entier',
                        'boolean' => 'Booléen',
                        'json' => 'JSON',
                        'color' => 'Couleur (hex)',
                        'url' => 'URL',
                    ])
                    ->required()
                    ->default('string')
                    ->live(),
                Forms\Components\Textarea::make('value')
                    ->label('Valeur')
                    ->required()
                    ->rows(3)
                    ->helperText(fn ($get) => match ($get('type')) {
                        'boolean' => 'true ou false',
                        'json' => 'Format JSON valide',
                        'color' => 'Format hex: #FF0000',
                        'url' => 'URL complète: https://...',
                        default => 'Valeur de la configuration',
                    }),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(2)
                    ->maxLength(500),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Clé')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valeur')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'string' => 'gray',
                        'integer' => 'info',
                        'boolean' => 'warning',
                        'json' => 'success',
                        'color' => 'danger',
                        'url' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'string' => 'Texte',
                        'integer' => 'Nombre entier',
                        'boolean' => 'Booléen',
                        'json' => 'JSON',
                        'color' => 'Couleur',
                        'url' => 'URL',
                    ]),
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
            ->defaultSort('key', 'asc');
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
            'index' => Pages\ManageAppConfigs::route('/'),
        ];
    }
}
