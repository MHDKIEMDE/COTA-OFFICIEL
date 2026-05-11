<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PredictionResource\Pages;
use App\Models\Prediction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PredictionResource extends Resource
{
    protected static ?string $model = Prediction::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Prédictions';
    protected static ?string $modelLabel = 'Prédiction';
    protected static ?string $pluralModelLabel = 'Prédictions';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Match')
                    ->schema([
                        Forms\Components\TextInput::make('match_id')
                            ->label('ID Match')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('home_team')
                            ->label('Équipe domicile')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('away_team')
                            ->label('Équipe extérieur')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('competition')
                            ->label('Compétition')
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('match_date')
                            ->label('Date du match')
                            ->required()
                            ->native(false),
                    ])->columns(2),

                Forms\Components\Section::make('Prédiction')
                    ->schema([
                        Forms\Components\Select::make('bet_type')
                            ->label('Type de pari')
                            ->options([
                                '1X2' => '1X2',
                                'BTTS' => 'BTTS (Les deux marquent)',
                                'Over/Under' => 'Over/Under',
                                'Double Chance' => 'Double Chance',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('prediction')
                            ->label('Prédiction')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('odds')
                            ->label('Cotes')
                            ->numeric()
                            ->step(0.01),
                        Forms\Components\Select::make('confidence_stars')
                            ->label('Confiance')
                            ->options([
                                1 => '1 étoile',
                                2 => '2 étoiles',
                                3 => '3 étoiles',
                                4 => '4 étoiles',
                                5 => '5 étoiles',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Statut')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'won' => 'Gagné',
                                'lost' => 'Perdu',
                                'cancelled' => 'Annulé',
                            ])
                            ->required()
                            ->default('pending'),
                        Forms\Components\TextInput::make('home_score')
                            ->label('Score domicile')
                            ->numeric(),
                        Forms\Components\TextInput::make('away_score')
                            ->label('Score extérieur')
                            ->numeric(),
                        Forms\Components\Toggle::make('is_published')
                            ->label('Publié')
                            ->default(false),
                        Forms\Components\Toggle::make('is_premium')
                            ->label('Premium')
                            ->default(false),
                    ])->columns(5),

                Forms\Components\Section::make('Scores algorithme')
                    ->schema([
                        Forms\Components\TextInput::make('score_form')
                            ->label('Forme')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('score_h2h')
                            ->label('H2H')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('score_home_away')
                            ->label('Domicile/Extérieur')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('score_league')
                            ->label('Ligue')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('score_goals')
                            ->label('Buts')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('score_time')
                            ->label('Temps')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('total_score')
                            ->label('Score total')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('home_team')
                    ->label('Domicile')
                    ->searchable(),
                Tables\Columns\TextColumn::make('away_team')
                    ->label('Extérieur')
                    ->searchable(),
                Tables\Columns\TextColumn::make('competition')
                    ->label('Compétition')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prediction')
                    ->label('Prédiction')
                    ->searchable(),
                Tables\Columns\TextColumn::make('odds')
                    ->label('Cotes')
                    ->sortable(),
                Tables\Columns\TextColumn::make('confidence_stars')
                    ->label('Confiance')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1', '2' => 'danger',
                        '3' => 'warning',
                        '4', '5' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'won' => 'success',
                        'lost' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Publié')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_premium')
                    ->label('Premium')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('match_date')
                    ->label('Date match')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'won' => 'Gagné',
                        'lost' => 'Perdu',
                        'cancelled' => 'Annulé',
                    ]),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Publié'),
                Tables\Filters\TernaryFilter::make('is_premium')
                    ->label('Premium'),
                Tables\Filters\Filter::make('match_date')
                    ->form([
                        Forms\Components\DatePicker::make('match_from')
                            ->label('Match à partir de'),
                        Forms\Components\DatePicker::make('match_until')
                            ->label('Match jusqu\'au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['match_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('match_date', '>=', $date),
                            )
                            ->when(
                                $data['match_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('match_date', '<=', $date),
                            );
                    }),
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
            ->defaultSort('match_date', 'desc');
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
            'index' => Pages\ListPredictions::route('/'),
            'create' => Pages\CreatePrediction::route('/create'),
            'edit' => Pages\EditPrediction::route('/{record}/edit'),
        ];
    }
}
