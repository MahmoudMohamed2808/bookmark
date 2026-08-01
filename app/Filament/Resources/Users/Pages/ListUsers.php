<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (): \Symfony\Component\HttpFoundation\StreamedResponse {
                    $csv = fopen('php://temp', 'w+');

                    fputcsv($csv, ['Name', 'Email', 'Verified', 'Joined']);

                    foreach (User::query()->withCount('bookmarks')->cursor() as $user) {
                        fputcsv($csv, [
                            $user->name,
                            $user->email,
                            $user->email_verified_at ? 'Yes' : 'No',
                            $user->created_at?->toDateTimeString(),
                        ]);
                    }

                    rewind($csv);

                    return response()->streamDownload(function () use ($csv): void {
                        fpassthru($csv);
                    }, 'users.csv', ['Content-Type' => 'text/csv']);
                }),
            CreateAction::make(),
        ];
    }
}
