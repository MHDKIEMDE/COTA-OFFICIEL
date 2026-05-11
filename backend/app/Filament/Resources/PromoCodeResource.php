<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoCodeResource\Pages;
use App\Models\PromoCodeUse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class PromoCodeResource extends Resource
{
    protected static ?string $model = PromoCodeUse::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Codes Promo';
    protected static ?string $modelLabel = 'Utilisation code promo';
    protected static ?string $pluralModelLabel = 'Utilisations codes promo';
    protected static ?int $navigationSort = 14;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('promo_code')->label('Code promo')->required(),
            Forms\Components\Select::make('bookmaker')->label('Bookmaker')->options([
                '1xbet' => '1xBet', 'betwinner' => 'BetWinner', 'melbet' => 'Melbet',
                'linebet' => 'LineBet', 'bet365' => 'Bet365', 'betway' => 'Betway',
                'sportybet' => 'SportyBet', '22bet' => '22Bet', 'parimatch' => 'Parimatch',
                'msport' => 'MSport', 'bwin' => 'Bwin', 'autre' => 'Autre',
            ]),
            Forms\Components\TextInput::make('phone')->label('Téléphone'),
            Forms\Components\DateTimePicker::make('used_at')->label('Date utilisation')->native(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('promo_code')->label('Code')->badge()->color('success')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Utilisateur')->searchable()->default('Anonyme'),
                Tables\Columns\TextColumn::make('phone')->label('Téléphone')->searchable(),
                Tables\Columns\TextColumn::make('bookmaker')->label('Bookmaker')->badge()->sortable(),
                Tables\Columns\TextColumn::make('used_at')->label('Date & heure')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('bookmaker')->options([
                    '1xbet' => '1xBet', 'betwinner' => 'BetWinner', 'melbet' => 'Melbet',
                    'linebet' => 'LineBet', 'bet365' => 'Bet365', 'betway' => 'Betway',
                    'sportybet' => 'SportyBet', '22bet' => '22Bet', 'parimatch' => 'Parimatch',
                    'msport' => 'MSport', 'bwin' => 'Bwin', 'autre' => 'Autre',
                ]),
                SelectFilter::make('promo_code')->options(fn () =>
                    PromoCodeUse::distinct()->pluck('promo_code', 'promo_code')->toArray()
                ),
            ])
            ->actions([Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('used_at', 'desc');
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPromoCodes::route('/')];
    }
}
