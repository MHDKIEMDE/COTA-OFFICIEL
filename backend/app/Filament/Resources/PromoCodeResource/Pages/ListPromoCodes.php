<?php

namespace App\Filament\Resources\PromoCodeResource\Pages;

use App\Filament\Resources\PromoCodeResource;
use App\Models\AppConfig;
use App\Models\PromoCodeUse;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListPromoCodes extends ListRecords
{
    protected static string $resource = PromoCodeResource::class;

    protected function getHeaderWidgets(): array { return []; }

    protected function getHeaderActions(): array
    {
        $currentCode = AppConfig::where('key', 'promo_code')->value('value') ?? 'CMD1122';

        return [
            Action::make('changePromoCode')
                ->label('Changer le code promo (actuel: ' . $currentCode . ')')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->form([
                    TextInput::make('new_code')
                        ->label('Nouveau code promo')
                        ->default($currentCode)
                        ->required()
                        ->minLength(4)
                        ->maxLength(20)
                        ->helperText('Ce code sera affiché dans l\'application pour tous les bookmakers'),
                ])
                ->action(function (array $data): void {
                    AppConfig::updateOrCreate(
                        ['key' => 'promo_code'],
                        ['value' => strtoupper(trim($data['new_code'])), 'type' => 'string', 'description' => 'Code promo affiché dans l\'app']
                    );
                    Notification::make()->title('Code promo mis à jour : ' . strtoupper($data['new_code']))->success()->send();
                }),
        ];
    }
}
