<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Models\ActivityLog;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bookmark.title')
                    ->label('Bookmark')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        'restored' => 'warning',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->limit(50)
                    ->placeholder('-'),
                TextColumn::make('ip_address')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Logged at'),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->options(fn (): array => ActivityLog::query()
                        ->distinct()
                        ->pluck('action', 'action')
                        ->all()),
                TernaryFilter::make('bookmark')
                    ->label('With bookmark')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('bookmark_id'),
                        false: fn ($query) => $query->whereNull('bookmark_id'),
                    ),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View details')
                    ->icon('heroicon-o-eye')
                    ->fillForm(fn (ActivityLog $record): array => [
                        'action' => $record->action,
                        'description' => $record->description,
                        'properties' => json_encode($record->properties, JSON_PRETTY_PRINT),
                    ])
                    ->modal()
                    ->form([
                        \Filament\Forms\Components\TextInput::make('action')
                            ->disabled(),
                        \Filament\Forms\Components\Textarea::make('description')
                            ->disabled()
                            ->columnSpanFull(),
                        \Filament\Forms\Components\Textarea::make('properties')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    Action::make('clearOld')
                        ->label('Clear older than 30 days')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->action(function (): void {
                            $deleted = ActivityLog::query()
                                ->where('created_at', '<', now()->subDays(30))
                                ->delete();

                            Notification::make()
                                ->title("Deleted {$deleted} log(s)")
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100]);
    }
}
