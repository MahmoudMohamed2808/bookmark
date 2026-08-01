<?php

namespace App\Filament\Resources\Collections\Tables;

use App\Models\Collection;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CollectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->icon(fn (Collection $record): ?string => $record->icon ?: null)
                    ->color(fn (Collection $record): ?string => $record->color ?: null),
                TextColumn::make('description')
                    ->limit(50)
                    ->placeholder('-')
                    ->toggleable(),
                IconColumn::make('is_shared')
                    ->boolean()
                    ->label('Shared'),
                TextColumn::make('bookmarks_count')
                    ->label('Bookmarks')
                    ->counts('bookmarks')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
