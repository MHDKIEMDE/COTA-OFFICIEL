<?php

namespace App\Filament\Resources\BookmakerResource\Pages;

use App\Filament\Resources\BookmakerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookmakers extends ListRecords
{
    protected static string $resource = BookmakerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
