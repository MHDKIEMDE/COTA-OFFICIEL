<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopReferrersWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static ?string $heading = 'Top parrains (30 derniers jours)';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\User::query()
                    ->withCount(['referrals as validated_referrals' => fn ($q) => $q->where('status', 'validated')])
                    ->having('validated_referrals', '>', 0)
                    ->orderByDesc('validated_referrals')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Utilisateur')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone'),
                Tables\Columns\TextColumn::make('referral_code')
                    ->label('Code')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('validated_referrals')
                    ->label('Filleuls validés')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_premium')
                    ->label('Premium')
                    ->boolean(),
            ])
            ->paginated(false);
    }
}
