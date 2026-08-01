<?php

namespace App\Filament\Resources\Snippets\Tables;

use App\Models\Snippet;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SnippetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'snippet' => 'info',
                        'command' => 'warning',
                        'terminal_note' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('language')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-'),
                TextColumn::make('category.name')
                    ->placeholder('-'),
                TextColumn::make('content')
                    ->limit(50)
                    ->copyable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('type')
                    ->options([
                        'snippet' => 'Snippet',
                        'command' => 'Command',
                        'terminal_note' => 'Terminal note',
                    ]),
                SelectFilter::make('language')
                    ->options(fn (): array => Snippet::query()
                        ->whereNotNull('language')
                        ->pluck('language', 'language')
                        ->all()),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (Action $action): void {
                        $record = $action->getRecord();

                        if (! $record instanceof Snippet) {
                            return;
                        }

                        $copy = $record->replicate(['content']);
                        $copy->title = $record->title.' (copy)';

                        $copy->save();

                        Notification::make()
                            ->title('Snippet duplicated')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    \Filament\Actions\ForceDeleteBulkAction::make(),
                    \Filament\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
