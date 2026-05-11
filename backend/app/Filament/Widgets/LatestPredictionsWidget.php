<?php

namespace App\Filament\Widgets;

use App\Models\Prediction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPredictionsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Dernières prédictions publiées';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Prediction::query()
                    ->where('is_published', true)
                    ->orderBy('match_date', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('match_date')
                    ->label('Date')
                    ->dateTime('d/m H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('home_team')
                    ->label('Domicile')
                    ->searchable(),
                Tables\Columns\TextColumn::make('away_team')
                    ->label('Extérieur')
                    ->searchable(),
                Tables\Columns\TextColumn::make('competition')
                    ->label('Compétition')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('bet_type')
                    ->label('Type'),
                Tables\Columns\TextColumn::make('prediction_outcome')
                    ->label('Pronostic')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('confidence_stars')
                    ->label('Étoiles')
                    ->formatStateUsing(fn ($state) => str_repeat('★', (int) $state) . str_repeat('☆', 4 - (int) $state)),
                Tables\Columns\TextColumn::make('result')
                    ->label('Résultat')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'correct'   => 'success',
                        'incorrect' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'correct'   => 'Gagné',
                        'incorrect' => 'Perdu',
                        default     => 'En attente',
                    }),
            ])
            ->paginated(false);
    }
}
