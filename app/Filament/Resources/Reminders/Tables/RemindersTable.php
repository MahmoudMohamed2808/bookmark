<?php

namespace App\Filament\Resources\Reminders\Tables;

use App\Models\Reminder;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RemindersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bookmark.title')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Reminder $record): ?string => $record->bookmark?->url),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'reminder' ? 'info' : 'warning'),
                TextColumn::make('remind_at')
                    ->dateTime()
                    ->sortable()
                    ->color(fn (Reminder $record): ?string => $record->is_notified ? null : ($record->remind_at->isPast() ? 'danger' : 'warning')),
                IconColumn::make('is_notified')
                    ->boolean()
                    ->label('Notified'),
                TextColumn::make('notified_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'reminder' => 'Reminder',
                        'scheduled_review' => 'Scheduled review',
                    ]),
                TernaryFilter::make('is_notified')
                    ->label('Notified'),
                TernaryFilter::make('overdue')
                    ->label('Overdue only')
                    ->queries(
                        true: fn ($query) => $query->where('is_notified', false)->where('remind_at', '<', now()),
                        false: fn ($query) => $query,
                    ),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('markNotified')
                    ->label('Mark as notified')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (Reminder $record): bool => ! $record->is_notified)
                    ->action(function (Action $action): void {
                        $record = $action->getRecord();

                        if (! $record instanceof Reminder) {
                            return;
                        }

                        $record->update([
                            'is_notified' => true,
                            'notified_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Reminder marked as notified')
                            ->success()
                            ->send();
                    }),
                Action::make('snooze')
                    ->label('Snooze')
                    ->icon('heroicon-o-bell-snooze')
                    ->visible(fn (Reminder $record): bool => ! $record->is_notified)
                    ->schema([
                        DateTimePicker::make('remind_at')
                            ->label('New reminder time')
                            ->required(),
                    ])
                    ->action(function (Action $action, array $data): void {
                        $record = $action->getRecord();

                        if (! $record instanceof Reminder) {
                            return;
                        }

                        $record->update(['remind_at' => $data['remind_at']]);

                        Notification::make()
                            ->title('Reminder snoozed')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('remind_at');
    }
}
