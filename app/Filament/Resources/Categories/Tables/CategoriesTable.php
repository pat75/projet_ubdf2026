<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')->label('Ordre')->sortable(),
                TextColumn::make('name')->label('Métier')->searchable(),
                TextColumn::make('slug')->label('Adresse')->searchable(),
                TextColumn::make('users_count')->label('Créatifs')->counts('users'),
                IconColumn::make('is_active')->label('Actif')->boolean(),
            ])
            ->defaultSort('position')
            ->recordActions([EditAction::make()]);
    }
}
