<?php

namespace App\Filament\Resources\Bookmarks;

use App\Filament\Resources\Bookmarks\Pages\CreateBookmark;
use App\Filament\Resources\Bookmarks\Pages\EditBookmark;
use App\Filament\Resources\Bookmarks\Pages\ListBookmarks;
use App\Filament\Resources\Bookmarks\Pages\ViewBookmark;
use App\Filament\Resources\Bookmarks\RelationManagers\ActivityLogsRelationManager;
use App\Filament\Resources\Bookmarks\RelationManagers\CollectionsRelationManager;
use App\Filament\Resources\Bookmarks\RelationManagers\RemindersRelationManager;
use App\Filament\Resources\Bookmarks\Schemas\BookmarkForm;
use App\Filament\Resources\Bookmarks\Schemas\BookmarkInfolist;
use App\Filament\Resources\Bookmarks\Tables\BookmarksTable;
use App\Models\Bookmark;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookmarkResource extends Resource
{
    protected static ?string $model = Bookmark::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookmark;

    protected static string|UnitEnum|null $navigationGroup = 'Library';

    public static function form(Schema $schema): Schema
    {
        return BookmarkForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BookmarkInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookmarksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CollectionsRelationManager::class,
            RemindersRelationManager::class,
            ActivityLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookmarks::route('/'),
            'create' => CreateBookmark::route('/create'),
            'view' => ViewBookmark::route('/{record}'),
            'edit' => EditBookmark::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
