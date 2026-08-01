<?php

namespace App\Filament\Resources\Snippets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SnippetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->options([
                        'snippet' => 'Snippet',
                        'command' => 'Command',
                        'terminal_note' => 'Terminal note',
                    ])
                    ->default('snippet')
                    ->required(),
                TextInput::make('language')
                    ->placeholder('php, javascript, bash...'),
                Textarea::make('content')
                    ->required()
                    ->rows(10)
                    ->columnSpanFull()
                    ->autosize(),
                Textarea::make('description')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }
}
