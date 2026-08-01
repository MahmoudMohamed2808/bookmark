<?php

namespace App\Filament\Resources\Tags\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Tags\Tag;

class TagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->formatStateUsing(fn ($state): string => is_array($state) ? (reset($state) ?: '') : $state)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('count')
                    ->label('Usage')
                    ->counts('taggables')
                    ->sortable(),
                TextColumn::make('order_column')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('rename')
                    ->label('Rename')
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        TextInput::make('name')
                            ->label('New name')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (Action $action, array $data): void {
                        $record = $action->getRecord();

                        if (! $record instanceof Tag) {
                            return;
                        }

                        $record->update(['name' => $data['name']]);

                        Notification::make()
                            ->title('Tag renamed')
                            ->success()
                            ->send();
                    }),
                Action::make('merge')
                    ->label('Merge into...')
                    ->icon('heroicon-o-arrow-right-start-on-rectangle')
                    ->requiresConfirmation()
                    ->form([
                        Select::make('target_tag_id')
                            ->label('Target tag')
                            ->options(fn (Action $action): array => Tag::query()
                                ->where('id', '!=', $action->getRecord()?->id)
                                ->get()
                                ->mapWithKeys(fn (Tag $tag): array => [
                                    $tag->id => is_array($tag->name) ? (reset($tag->name) ?: $tag->slug) : $tag->name,
                                ])
                                ->all())
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (Action $action, array $data): void {
                        $source = $action->getRecord();

                        if (! $source instanceof Tag) {
                            return;
                        }

                        $target = Tag::find($data['target_tag_id']);

                        if (! $target) {
                            return;
                        }

                        $moved = 0;

                        foreach ($source->taggables()->with('taggable')->get() as $taggable) {
                            $model = $taggable->taggable;

                            if (! $model) {
                                continue;
                            }

                            $model->attachTag($target->name, $target->type);

                            $moved++;
                        }

                        $source->delete();

                        Notification::make()
                            ->title("Merged {$moved} record(s) into target tag")
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
            ->defaultSort('order_column');
    }
}
