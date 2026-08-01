<?php

namespace App\Filament\Resources\Reminders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReminderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('bookmark_id')
                    ->label('Bookmark')
                    ->relationship('bookmark', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('type')
                    ->options([
                        'reminder' => 'Reminder',
                        'scheduled_review' => 'Scheduled review',
                    ])
                    ->default('reminder')
                    ->required(),
                DateTimePicker::make('remind_at')
                    ->required(),
                Toggle::make('is_notified')
                    ->disabled(),
                DateTimePicker::make('notified_at')
                    ->disabled(),
            ]);
    }
}
