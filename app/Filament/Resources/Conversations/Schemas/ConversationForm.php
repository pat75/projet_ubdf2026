<?php

namespace App\Filament\Resources\Conversations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ConversationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('legacy_id')
                    ->numeric(),
                Select::make('user_id')
                    ->relationship('user', 'id')
                    ->required(),
                TextInput::make('channel')
                    ->required()
                    ->default('contact'),
                TextInput::make('subject'),
                TextInput::make('request_detail'),
                TextInput::make('sender_name'),
                TextInput::make('sender_company'),
                TextInput::make('sender_email')
                    ->email(),
                TextInput::make('sender_phone')
                    ->tel(),
                TextInput::make('selector'),
                FileUpload::make('book_image')
                    ->image(),
                Toggle::make('is_spam')
                    ->required(),
                DateTimePicker::make('last_message_at'),
            ]);
    }
}
