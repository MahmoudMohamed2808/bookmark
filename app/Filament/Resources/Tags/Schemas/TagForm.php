<?php

namespace App\Filament\Resources\Tags\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->formatStateUsing(fn ($state): ?string => is_array($state) ? (reset($state) ?: null) : $state),
                Select::make('type')
                    ->options(fn (): array => \Spatie\Tags\Tag::getTypes()
                        ->mapWithKeys(fn (?string $type): array => [$type ?? 'null' => $type ?? 'General'])
                        ->all())
                    ->placeholder('General'),
                TextInput::make('order_column')
                    ->label('Sort order')
                    ->numeric(),
            ]);
    }
}
