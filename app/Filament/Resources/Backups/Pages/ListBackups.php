<?php

namespace App\Filament\Resources\Backups\Pages;

use App\Filament\Resources\Backups\BackupResource;
use App\Services\BackupService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListBackups extends ListRecords
{
    protected static string $resource = BackupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label('Generate backup')
                ->icon('heroicon-o-plus')
                ->form([
                    Select::make('type')
                        ->options([
                            'json' => 'JSON',
                            'csv' => 'CSV',
                            'html' => 'HTML',
                        ])
                        ->default('json')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    app(BackupService::class)->generate(auth()->user(), $data['type']);

                    Notification::make()
                        ->title('Backup generated')
                        ->success()
                        ->send();
                }),
        ];
    }
}
