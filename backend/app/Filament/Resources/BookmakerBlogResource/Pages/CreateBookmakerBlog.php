<?php

namespace App\Filament\Resources\BookmakerBlogResource\Pages;

use App\Filament\Resources\BookmakerBlogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBookmakerBlog extends CreateRecord
{
    protected static string $resource = BookmakerBlogResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
