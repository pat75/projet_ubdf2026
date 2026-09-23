<?php

namespace App\Filament\Resources\Admins\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdminsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nom')->searchable(),
                TextColumn::make('email')->label('E-mail')->searchable(),
                IconColumn::make('is_active')->label('Actif')->boolean(),
                TextColumn::make('created_at')->label('Créé le')->date('d/m/Y'),
            ])
            ->recordActions([EditAction::make()]);
    }
}
