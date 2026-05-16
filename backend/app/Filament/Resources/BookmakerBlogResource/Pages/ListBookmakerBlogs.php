<?php

namespace App\Filament\Resources\BookmakerBlogResource\Pages;

use App\Filament\Resources\BookmakerBlogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookmakerBlogs extends ListRecords
{
    protected static string $resource = BookmakerBlogResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
