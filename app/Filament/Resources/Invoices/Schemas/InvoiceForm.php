<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('legacy_id')
                    ->numeric(),
                TextInput::make('legacy_source'),
                Select::make('user_id')
                    ->relationship('user', 'id'),
                TextInput::make('brand')
                    ->required()
                    ->default('ub'),
                TextInput::make('number')
                    ->required(),
                TextInput::make('label'),
                TextInput::make('designation'),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('vat')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('currency')
                    ->required()
                    ->default('EUR'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('gateway'),
                TextInput::make('gateway_payload'),
                DateTimePicker::make('issued_at'),
                DateTimePicker::make('paid_at'),
            ]);
    }
}
