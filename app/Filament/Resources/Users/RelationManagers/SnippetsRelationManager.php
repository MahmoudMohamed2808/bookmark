<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Snippets\Schemas\SnippetForm;
use App\Filament\Resources\Snippets\Tables\SnippetsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SnippetsRelationManager extends RelationManager
{
    protected static string $relationship = 'snippets';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Schema $schema): Schema
    {
        return SnippetForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return SnippetsTable::configure($table);
    }
}
