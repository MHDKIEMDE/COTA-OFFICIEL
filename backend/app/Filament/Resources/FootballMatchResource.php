<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FootballMatchResource\Pages;
use App\Models\FootballMatch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FootballMatchResource extends Resource
{
    protected static ?string $model = FootballMatch::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'Matchs';
    protected static ?string $modelLabel = 'Match';
    protected static ?string $pluralModelLabel = 'Matchs';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Match')
                    ->schema([
                        Forms\Components\TextInput::make('match_id')
                            ->label('ID Match (API)')
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

                Forms\Components\Section::make('Scores')
                    ->schema([
                        Forms\Components\TextInput::make('home_score')
                            ->label('Score domicile')
                            ->numeric(),
                        Forms\Components\TextInput::make('away_score')
                            ->label('Score extérieur')
                            ->numeric(),
                        Forms\Components\TextInput::make('home_score_halftime')
                            ->label('Score mi-temps domicile')
                            ->numeric(),
                        Forms\Components\TextInput::make('away_score_halftime')
                            ->label('Score mi-temps extérieur')
                            ->numeric(),
                    ])->columns(4),

                Forms\Components\Section::make('Statut')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'scheduled' => 'Programmé',
                                'live' => 'En direct',
                                'halftime' => 'Mi-temps',
                                'finished' => 'Terminé',
                                'postponed' => 'Reporté',
                                'cancelled' => 'Annulé',
                                'abandoned' => 'Abandonné',
                            ])
                            ->required()
                            ->default('scheduled'),
                        Forms\Components\TextInput::make('elapsed_time')
                            ->label('Temps écoulé (min)')
                            ->numeric(),
                        Forms\Components\TextInput::make('status_long')
                            ->label('Statut détaillé')
                            ->maxLength(255),
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
                Tables\Columns\TextColumn::make('home_score')
                    ->label('Score')
                    ->formatStateUsing(fn ($record) => ($record->home_score ?? '-') . ' - ' . ($record->away_score ?? '-')),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'live' => 'danger',
                        'halftime' => 'warning',
                        'finished' => 'success',
                        'scheduled' => 'info',
                        'postponed', 'cancelled', 'abandoned' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('match_date')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Programmé',
                        'live' => 'En direct',
                        'halftime' => 'Mi-temps',
                        'finished' => 'Terminé',
                        'postponed' => 'Reporté',
                        'cancelled' => 'Annulé',
                        'abandoned' => 'Abandonné',
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
            'index' => Pages\ListFootballMatches::route('/'),
            'create' => Pages\CreateFootballMatch::route('/create'),
            'edit' => Pages\EditFootballMatch::route('/{record}/edit'),
        ];
    }
}
