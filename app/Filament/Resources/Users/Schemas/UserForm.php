<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        DateTimePicker::make('email_verified_at'),
                    ]),
                Section::make('Password')
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->rule(Password::defaults()),
                    ]),
                Section::make('Two-factor authentication')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        Textarea::make('two_factor_secret')
                            ->disabled()
                            ->columnSpanFull(),
                        Textarea::make('two_factor_recovery_codes')
                            ->disabled()
                            ->columnSpanFull(),
                        DateTimePicker::make('two_factor_confirmed_at')
                            ->disabled(),
                    ]),
            ]);
    }
}
