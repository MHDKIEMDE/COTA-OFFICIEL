<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralResource\Pages;
use App\Models\Referral;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReferralResource extends Resource
{
    protected static ?string $model = Referral::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Parrainages';
    protected static ?string $modelLabel = 'Parrainage';
    protected static ?string $pluralModelLabel = 'Parrainages';
    protected static ?int $navigationSort = 13;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Parrainage')
                    ->schema([
                        Forms\Components\Select::make('referrer_id')
                            ->label('Parrain')
                            ->relationship('referrer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('referred_id')
                            ->label('Filleul')
                            ->relationship('referred', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Statut')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'validated' => 'Validé',
                                'rewarded' => 'Récompensé',
                            ])
                            ->required()
                            ->default('pending'),
                        Forms\Components\DateTimePicker::make('validated_at')
                            ->label('Validé le')
                            ->native(false),
                    ])->columns(2),

                Forms\Components\Section::make('Récompense')
                    ->schema([
                        Forms\Components\TextInput::make('reward_days')
                            ->label('Jours premium gagnés')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Select::make('reward_tier')
                            ->label('Niveau récompense')
                            ->options([
                                'first' => '1er filleul',
                                'tier_3' => '3e filleul',
                                'tier_5' => '5e filleul',
                                'tier_10' => '10e filleul',
                            ]),
                        Forms\Components\Toggle::make('reward_applied')
                            ->label('Récompense appliquée')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('reward_applied_at')
                            ->label('Récompense appliquée le')
                            ->native(false),
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
                Tables\Columns\TextColumn::make('referrer.name')
                    ->label('Parrain')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('referred.name')
                    ->label('Filleul')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'rewarded' => 'success',
                        'validated' => 'info',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('reward_days')
                    ->label('Jours gagnés')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reward_tier')
                    ->label('Niveau')
                    ->badge(),
                Tables\Columns\IconColumn::make('reward_applied')
                    ->label('Appliqué')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'validated' => 'Validé',
                        'rewarded' => 'Récompensé',
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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListReferrals::route('/'),
            'create' => Pages\CreateReferral::route('/create'),
            'edit' => Pages\EditReferral::route('/{record}/edit'),
        ];
    }
}
