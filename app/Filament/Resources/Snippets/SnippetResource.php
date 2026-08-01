<?php

namespace App\Filament\Resources\Snippets;

use App\Filament\Resources\Snippets\Pages\CreateSnippet;
use App\Filament\Resources\Snippets\Pages\EditSnippet;
use App\Filament\Resources\Snippets\Pages\ListSnippets;
use App\Filament\Resources\Snippets\Schemas\SnippetForm;
use App\Filament\Resources\Snippets\Tables\SnippetsTable;
use App\Models\Snippet;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SnippetResource extends Resource
{
    protected static ?string $model = Snippet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCodeBracket;

    protected static string|UnitEnum|null $navigationGroup = 'Library';

    public static function form(Schema $schema): Schema
    {
        return SnippetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SnippetsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSnippets::route('/'),
            'create' => CreateSnippet::route('/create'),
            'edit' => EditSnippet::route('/{record}/edit'),
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
