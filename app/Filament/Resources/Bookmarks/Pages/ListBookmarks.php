<?php

namespace App\Filament\Resources\Bookmarks\Pages;

use App\Filament\Resources\Bookmarks\BookmarkResource;
use App\Models\Bookmark;
use App\Services\ActivityLogService;
use App\Services\BookmarkImportService;
use App\Services\DuplicateDetectionService;
use App\Services\UrlNormalizer;
use App\Services\UrlValidationService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListBookmarks extends ListRecords
{
    protected static string $resource = BookmarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('quickAdd')
                ->label('Quick add')
                ->icon('heroicon-o-plus')
                ->schema([
                    TextInput::make('url')
                        ->url()
                        ->required(),
                    TextInput::make('title'),
                ])
                ->action(function (array $data): void {
                    $normalizer = app(UrlNormalizer::class);
                    $normalizedUrl = $normalizer->normalize($data['url']);

                    if (! app(UrlValidationService::class)->isValid($normalizedUrl)) {
                        Notification::make()
                            ->title('Invalid URL')
                            ->danger()
                            ->send();

                        return;
                    }

                    if (app(DuplicateDetectionService::class)->findDuplicate($normalizedUrl, auth()->id())) {
                        Notification::make()
                            ->title('A bookmark with this URL already exists')
                            ->warning()
                            ->send();

                        return;
                    }

                    $bookmark = auth()->user()->bookmarks()->create([
                        'url' => $normalizedUrl,
                        'url_hash' => $normalizer->hash($normalizedUrl),
                        'title' => $data['title'] ?: $normalizedUrl,
                    ]);

                    app(ActivityLogService::class)->log(
                        auth()->user(),
                        'created',
                        $bookmark,
                    );

                    Notification::make()
                        ->title('Bookmark added')
                        ->success()
                        ->send();
                }),
            Action::make('importHtml')
                ->label('Import HTML')
                ->icon('heroicon-o-arrow-up-tray')
                ->schema([
                    FileUpload::make('file')
                        ->label('Netscape bookmarks file')
                        ->acceptedFileTypes(['text/html'])
                        ->maxSize(10240)
                        ->storeFiles(false)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $imported = app(BookmarkImportService::class)->importFromHtml($data['file'], auth()->user());

                    Notification::make()
                        ->title("Imported {$imported} bookmark(s)")
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
