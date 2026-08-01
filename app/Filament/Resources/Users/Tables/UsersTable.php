<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('email_verified_at')
                    ->boolean()
                    ->label('Verified'),
                TextColumn::make('bookmarks_count')
                    ->label('Bookmarks')
                    ->counts('bookmarks')
                    ->sortable(),
                TextColumn::make('collections_count')
                    ->label('Collections')
                    ->counts('collections')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Joined'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('impersonate')
                    ->label('Impersonate')
                    ->icon('heroicon-o-user-circle')
                    ->requiresConfirmation()
                    ->action(function (Action $action): void {
                        $record = $action->getRecord();

                        if (! $record instanceof User) {
                            return;
                        }

                        auth()->login($record);

                        Notification::make()
                            ->title("Now impersonating {$record->name}")
                            ->success()
                            ->send();
                    }),
                Action::make('verifyEmail')
                    ->label('Verify email')
                    ->icon('heroicon-o-envelope-open')
                    ->visible(fn (User $record): bool => $record->email_verified_at === null)
                    ->action(function (Action $action): void {
                        $record = $action->getRecord();

                        if (! $record instanceof User) {
                            return;
                        }

                        $record->forceFill(['email_verified_at' => now()])->save();

                        Notification::make()
                            ->title('Email verified')
                            ->success()
                            ->send();
                    }),
                Action::make('resetPassword')
                    ->label('Send password reset')
                    ->icon('heroicon-o-key')
                    ->requiresConfirmation()
                    ->action(function (Action $action): void {
                        $record = $action->getRecord();

                        if (! $record instanceof User) {
                            return;
                        }

                        \Illuminate\Support\Facades\Password::broker()->sendResetLink(['email' => $record->email]);

                        Notification::make()
                            ->title('Password reset link sent')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
