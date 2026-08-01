<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\Resources\Bookmarks\Schemas\BookmarkForm;
use App\Filament\Resources\Bookmarks\Tables\BookmarksTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class BookmarksRelationManager extends RelationManager
{
    protected static string $relationship = 'bookmarks';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Schema $schema): Schema
    {
        return BookmarkForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return BookmarksTable::configure($table);
    }
}
