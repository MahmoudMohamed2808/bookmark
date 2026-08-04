<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('parent_id')
                    ->label('Parent category')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->options(fn (): array => Category::query()
                        ->where('parent_id', null)
                        ->pluck('name', 'id')
                        ->all()),
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
                TextInput::make('icon')
                    ->placeholder('heroicon-o-folder')
                    ->rules(['regex:/^(heroicon-o-)?o-[a-z0-9-]+$/'])
                    ->validationMessages([
                        'regex' => 'The icon must be a valid Heroicon name, e.g. heroicon-o-folder.',
                    ]),
                ColorPicker::make('color'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
