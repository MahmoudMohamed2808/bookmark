<?php

namespace App\Filament\Resources\Collections\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, string $state, Set $set): void {
                        if ($operation !== 'create') {
                            return;
                        }

                        $set('slug', str($state)->slug()->toString());
                    }),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('icon')
                    ->placeholder('heroicon-o-folder')
                    ->rules(['regex:/^(heroicon-o-)?o-[a-z0-9-]+$/'])
                    ->validationMessages([
                        'regex' => 'The icon must be a valid Heroicon name, e.g. heroicon-o-folder.',
                    ]),
                ColorPicker::make('color'),
                Toggle::make('is_shared')
                    ->default(false),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
