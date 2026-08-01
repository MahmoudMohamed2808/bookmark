<?php

namespace App\Filament\Resources\Backups\Tables;

use App\Models\Backup;
use App\Services\BackupService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class BackupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'json' => 'info',
                        'csv' => 'warning',
                        'html' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('error')
                    ->limit(60)
                    ->placeholder('-'),
                TextColumn::make('file_path')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Created'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'json' => 'JSON',
                        'csv' => 'CSV',
                        'html' => 'HTML',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (Backup $record): bool => $record->status === 'completed' && filled($record->file_path))
                    ->action(function (Action $action) {
                        $record = $action->getRecord();

                        if (! $record instanceof Backup) {
                            return;
                        }

                        if (! Storage::disk('local')->exists($record->file_path)) {
                            Notification::make()
                                ->title('Backup file not found')
                                ->danger()
                                ->send();

                            return;
                        }

                        return response()->download(
                            Storage::disk('local')->path($record->file_path),
                            basename($record->file_path),
                        );
                    }),
                Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (Backup $record): bool => $record->status === 'failed')
                    ->action(function (Action $action): void {
                        $record = $action->getRecord();

                        if (! $record instanceof Backup) {
                            return;
                        }

                        app(BackupService::class)->generate($record->user, $record->type);

                        $record->delete();

                        Notification::make()
                            ->title('Backup regenerated')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
