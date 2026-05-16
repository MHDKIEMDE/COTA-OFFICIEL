<?php

namespace App\Filament\Resources\BookmakerBlogResource\Pages;

use App\Filament\Resources\BookmakerBlogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookmakerBlog extends EditRecord
{
    protected static string $resource = BookmakerBlogResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
