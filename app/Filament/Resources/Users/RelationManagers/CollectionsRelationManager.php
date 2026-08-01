<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Collections\Schemas\CollectionForm;
use App\Filament\Resources\Collections\Tables\CollectionsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CollectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'collections';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return CollectionForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return CollectionsTable::configure($table);
    }
}
