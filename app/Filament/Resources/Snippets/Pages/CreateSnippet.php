<?php

namespace App\Filament\Resources\Snippets\Pages;

use App\Filament\Resources\Snippets\SnippetResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSnippet extends CreateRecord
{
    protected static string $resource = SnippetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}
