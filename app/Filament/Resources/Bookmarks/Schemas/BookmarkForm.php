<?php

namespace App\Filament\Resources\Bookmarks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\SpatieLaravelTagsPlugin\Types\AllTagTypes;

class BookmarkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('url')
                            ->url()
                            ->required()
                            ->columnSpanFull(),
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required(),
                            ]),
                        SpatieTagsInput::make('tags')
                            ->type(AllTagTypes::make())
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        MarkdownEditor::make('notes')
                            ->columnSpanFull(),
                        SpatieMediaLibraryFileUpload::make('screenshot')
                            ->collection('screenshots')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),
                Section::make('Status')
                    ->columns(2)
                    ->schema([
                        Toggle::make('pinned_at')
                            ->label('Pinned')
                            ->formatStateUsing(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => $state ? now() : null),
                        Toggle::make('favorited_at')
                            ->label('Favorite')
                            ->formatStateUsing(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => $state ? now() : null),
                        Toggle::make('read_later_at')
                            ->label('Read later')
                            ->formatStateUsing(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => $state ? now() : null),
                        Toggle::make('archived_at')
                            ->label('Archived')
                            ->formatStateUsing(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => $state ? now() : null),
                        DateTimePicker::make('reminder_at'),
                        DateTimePicker::make('expires_at'),
                    ]),
                Section::make('System')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextInput::make('favicon')
                            ->disabled(),
                        TextInput::make('url_hash')
                            ->disabled(),
                        TextInput::make('http_status')
                            ->numeric()
                            ->disabled()
                            ->label('HTTP status'),
                        Toggle::make('is_broken')
                            ->disabled(),
                        DateTimePicker::make('last_checked_at')
                            ->disabled(),
                        TextInput::make('visits_count')
                            ->numeric()
                            ->disabled()
                            ->default(0),
                        DateTimePicker::make('last_visited_at')
                            ->disabled(),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
