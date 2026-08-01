<?php

namespace App\Filament\Resources\Snippets\Pages;

use App\Filament\Resources\Snippets\SnippetResource;
use App\Models\Snippet;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSnippets extends ListRecords
{
    protected static string $resource = SnippetResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(fn (): int => Snippet::count()),
            'snippets' => Tab::make('Snippets')
                ->badge(fn (): int => Snippet::where('type', 'snippet')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'snippet')),
            'commands' => Tab::make('Commands')
                ->badge(fn (): int => Snippet::where('type', 'command')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'command')),
            'terminal_notes' => Tab::make('Terminal notes')
                ->badge(fn (): int => Snippet::where('type', 'terminal_note')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'terminal_note')),
        ];
    }
}
