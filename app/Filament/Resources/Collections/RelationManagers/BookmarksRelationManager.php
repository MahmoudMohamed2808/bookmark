<?php

namespace App\Filament\Resources\Collections\RelationManagers;

use App\Models\Bookmark;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookmarksRelationManager extends RelationManager
{
    protected static string $relationship = 'bookmarks';

    protected static ?string $recordTitleAttribute = 'title';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Bookmark $record): ?string => $record->url),
                TextColumn::make('category.name')
                    ->placeholder('-'),
                IconColumn::make('pinned_at')
                    ->boolean()
                    ->label('Pinned'),
                TextColumn::make('visits_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                AttachAction::make()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->relationship('bookmarks', 'title')
                            ->searchable()
                            ->preload(),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ]),
                DetachBulkAction::make(),
            ]);
    }
}
