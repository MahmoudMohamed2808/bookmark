<?php

namespace App\Filament\Resources\Bookmarks\RelationManagers;

use App\Filament\Resources\Reminders\Schemas\ReminderForm;
use App\Filament\Resources\Reminders\Tables\RemindersTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RemindersRelationManager extends RelationManager
{
    protected static string $relationship = 'reminders';

    protected static ?string $recordTitleAttribute = 'remind_at';

    public function form(Schema $schema): Schema
    {
        return ReminderForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return RemindersTable::configure($table);
    }
}
