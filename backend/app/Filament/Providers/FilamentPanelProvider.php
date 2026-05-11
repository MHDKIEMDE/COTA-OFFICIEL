<?php

namespace App\Filament\Providers;

use App\Models\User;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Support\Facades\Gate;

class FilamentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('COTA Admin')
            ->colors([
                'primary' => '#00CED1',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->middleware([
                \App\Http\Middleware\SuperAdminMiddleware::class,
            ])
            ->authGuard('web')
            ->authPasswordBroker('users')
            ->userMenuItems([
                'profile' => false,
            ])
            ->navigationGroups([
                'Gestion',
                'Contenu',
                'Configuration',
                'Support',
            ]);
    }
}
