<?php

namespace App\Filament\Resources\Bookmarks\Schemas;

use App\Models\Bookmark;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\SpatieTagsEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class BookmarkInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('favicon')
                    ->circular()
                    ->hiddenLabel()
                    ->placeholder('-'),
                TextEntry::make('title')
                    ->hiddenLabel()
                    ->size(TextSize::Large)
                    ->weight(FontWeight::Bold),
                TextEntry::make('url')
                    ->label('URL')
                    ->color('primary')
                    ->columnSpanFull(),
                Section::make('Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('category.name')
                            ->label('Category')
                            ->badge()
                            ->color('info')
                            ->placeholder('-'),
                        SpatieTagsEntry::make('tags'),
                        TextEntry::make('description')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('notes')
                            ->markdown()
                            ->placeholder('-')
                            ->columnSpanFull(),
                        SpatieMediaLibraryImageEntry::make('screenshot')
                            ->label('Screenshots')
                            ->collection('screenshots')
                            ->columnSpanFull(),
                    ]),
                Section::make('Status')
                    ->columns(2)
                    ->schema([
                        IconEntry::make('pinned_at')
                            ->boolean()
                            ->label('Pinned'),
                        IconEntry::make('favorited_at')
                            ->boolean()
                            ->label('Favorite'),
                        IconEntry::make('read_later_at')
                            ->boolean()
                            ->label('Read later'),
                        IconEntry::make('archived_at')
                            ->boolean()
                            ->label('Archived'),
                        TextEntry::make('reminder_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('expires_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
                Section::make('Link health')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('http_status')
                            ->badge()
                            ->color(fn (?int $state): string => match (true) {
                                $state !== null && $state < 400 => 'success',
                                $state !== null && $state < 500 => 'warning',
                                $state !== null => 'danger',
                                default => 'gray',
                            })
                            ->placeholder('-'),
                        IconEntry::make('is_broken')
                            ->boolean()
                            ->label('Broken'),
                        TextEntry::make('last_checked_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
                Section::make('Usage')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('visits_count')
                            ->numeric(),
                        TextEntry::make('last_visited_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->placeholder('-')
                    ->visible(fn (Bookmark $record): bool => $record->trashed()),
            ]);
    }
}
