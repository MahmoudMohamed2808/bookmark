<?php

namespace App\Filament\Resources\Bookmarks\Tables;

use App\Models\Bookmark;
use App\Models\Collection;
use App\Services\ActivityLogService;
use App\Services\BookmarkMetadataService;
use App\Services\LinkHealthService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Notifications\Notification;
use Filament\SpatieLaravelTagsPlugin\Types\AllTagTypes;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SpatieTagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Actions\BulkAction;

class BookmarksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('favicon')
                    ->circular()
                    ->placeholder('-'),
                TextColumn::make('title')
                    ->icon('heroicon-o-link')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Bookmark $record): ?string => $record->url),
                TextColumn::make('category.name')
                    ->badge()
                    ->color('info')
                    ->placeholder('-'),
                SpatieTagsColumn::make('tags')
                    ->type(AllTagTypes::make())
                    ->toggleable(),
                IconColumn::make('pinned_at')
                    ->boolean()
                    ->label('Pinned'),
                IconColumn::make('favorited_at')
                    ->boolean()
                    ->label('Favorite'),
                IconColumn::make('read_later_at')
                    ->boolean()
                    ->label('Read later'),
                TextColumn::make('http_status')
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state !== null && $state < 400 => 'success',
                        $state !== null && $state < 500 => 'warning',
                        $state !== null => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('-'),
                IconColumn::make('is_broken')
                    ->boolean()
                    ->label('Broken'),
                TextColumn::make('visits_count')
                    ->numeric()
                    ->sortable()
                    ->label('Visits'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_broken')
                    ->label('Broken only'),
                SelectFilter::make('status')
                    ->options([
                        'pinned' => 'Pinned',
                        'favorited' => 'Favorited',
                        'read_later' => 'Read later',
                        'archived' => 'Archived',
                    ])
                    ->query(function ($query, array $data): void {
                        $value = $data['value'] ?? null;

                        if (! $value) {
                            return;
                        }

                        $query->when($value, function ($query, string $status): void {
                            match ($status) {
                                'pinned' => $query->whereNotNull('pinned_at'),
                                'favorited' => $query->whereNotNull('favorited_at'),
                                'read_later' => $query->whereNotNull('read_later_at'),
                                'archived' => $query->whereNotNull('archived_at'),
                                default => null,
                            };
                        });
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('fetchMetadata')
                    ->label('Fetch metadata')
                    ->icon('heroicon-o-sparkles')
                    ->color('info')
                    ->action(function (Action $action): void {
                        $record = $action->getRecord();

                        if (! $record instanceof Bookmark) {
                            return;
                        }

                        try {
                            $metadata = app(BookmarkMetadataService::class)->fetch($record->url);

                            $record->update([
                                'title' => $metadata['title'] ?? $record->title,
                                'description' => $metadata['description'] ?? $record->description,
                                'favicon' => $metadata['favicon'] ?? $record->favicon,
                            ]);

                            app(ActivityLogService::class)->log(
                                auth()->user(),
                                'metadata_fetched',
                                $record,
                            );

                            Notification::make()
                                ->title('Metadata fetched')
                                ->success()
                                ->send();
                        } catch (\Throwable $exception) {
                            Notification::make()
                                ->title('Could not fetch metadata')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('checkHealth')
                    ->label('Check link')
                    ->icon('heroicon-o-heart')
                    ->color('info')
                    ->action(function (Action $action): void {
                        $record = $action->getRecord();

                        if (! $record instanceof Bookmark) {
                            return;
                        }

                        $isHealthy = app(LinkHealthService::class)->check($record);

                        Notification::make()
                            ->title($isHealthy ? 'Link is healthy' : 'Link is broken')
                            ->success($isHealthy)
                            ->danger(! $isHealthy)
                            ->send();
                    }),
                Action::make('togglePin')
                    ->label(fn (Bookmark $record): string => $record->isPinned() ? 'Unpin' : 'Pin')
                    ->icon(fn (Bookmark $record): string => $record->isPinned() ? 'heroicon-o-arrow-up-circle' : 'heroicon-o-arrow-down-circle')
                    ->action(function (Action $action): void {
                        $record = $action->getRecord();

                        if (! $record instanceof Bookmark) {
                            return;
                        }

                        $record->update(['pinned_at' => $record->isPinned() ? null : now()]);
                    }),
                Action::make('toggleFavorite')
                    ->label(fn (Bookmark $record): string => $record->isFavorited() ? 'Unfavorite' : 'Favorite')
                    ->icon(fn (Bookmark $record): string => $record->isFavorited() ? 'heroicon-o-heart' : 'heroicon-o-star')
                    ->action(function (Action $action): void {
                        $record = $action->getRecord();

                        if (! $record instanceof Bookmark) {
                            return;
                        }

                        $record->update(['favorited_at' => $record->isFavorited() ? null : now()]);
                    }),
                Action::make('toggleReadLater')
                    ->label(fn (Bookmark $record): string => $record->isInReadLater() ? 'Remove from read later' : 'Read later')
                    ->icon('heroicon-o-clock')
                    ->action(function (Action $action): void {
                        $record = $action->getRecord();

                        if (! $record instanceof Bookmark) {
                            return;
                        }

                        $record->update(['read_later_at' => $record->isInReadLater() ? null : now()]);
                    }),
                Action::make('toggleArchive')
                    ->label(fn (Bookmark $record): string => $record->isArchived() ? 'Restore from archive' : 'Archive')
                    ->icon('heroicon-o-archive-box')
                    ->action(function (Action $action): void {
                        $record = $action->getRecord();

                        if (! $record instanceof Bookmark) {
                            return;
                        }

                        $record->update(['archived_at' => $record->isArchived() ? null : now()]);
                    }),
                Action::make('delete')
                    ->label(fn (Bookmark $record): string => $record->trashed() ? 'Force delete' : 'Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Action $action): void {
                        $record = $action->getRecord();

                        if (! $record instanceof Bookmark) {
                            return;
                        }

                        $record->trashed() ? $record->forceDelete() : $record->delete();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('archiveBulk')
                        ->label('Archive')
                        ->icon('heroicon-o-archive-box')
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records): void {
                            $records->each(function (Bookmark $record): void {
                                $record->update(['archived_at' => $record->archived_at ?? now()]);
                            });

                            Notification::make()
                                ->title($records->count().' bookmark(s) archived')
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('addToCollectionBulk')
                        ->label('Add to collection')
                        ->icon('heroicon-o-folder-plus')
                        ->deselectRecordsAfterCompletion()
                        ->schema([
                            Select::make('collection_id')
                                ->label('Collection')
                                ->options(fn (): array => Collection::query()
                                    ->pluck('name', 'id')
                                    ->all())
                                ->searchable()
                                ->required(),
                        ])
                        ->action(function (array $data, $records): void {
                            $collection = Collection::find($data['collection_id']);

                            if (! $collection) {
                                return;
                            }

                            $collection->bookmarks()->syncWithoutDetaching($records->pluck('id'));
                        }),
                    BulkAction::make('addTagsBulk')
                        ->label('Add tags')
                        ->icon('heroicon-o-tag')
                        ->deselectRecordsAfterCompletion()
                        ->schema([
                            SpatieTagsInput::make('tags')
                                ->type(AllTagTypes::make())
                                ->required(),
                        ])
                        ->action(function (array $data, $records): void {
                            $records->each(function (Bookmark $record) use ($data): void {
                                $record->attachTags($data['tags']);
                            });
                        }),
                    BulkAction::make('checkLinksBulk')
                        ->label('Check links')
                        ->icon('heroicon-o-heart')
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records): void {
                            app(LinkHealthService::class)->checkMany($records);

                            Notification::make()
                                ->title('Link health check finished')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
