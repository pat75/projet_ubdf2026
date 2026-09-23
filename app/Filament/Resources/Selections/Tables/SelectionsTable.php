<?php

namespace App\Filament\Resources\Selections\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SelectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('starts_at')->label('Du')->date('d/m/Y')->sortable(),
                TextColumn::make('ends_at')->label('Au')->date('d/m/Y'),
                TextColumn::make('brand')->label('Marque')->badge()
                    ->formatStateUsing(fn (?string $state) => $state === 'df' ? 'Dustfolio' : 'Ultra-book'),
                TextColumn::make('category_slug')->label('Métier')->placeholder('Tous'),
                TextColumn::make('users_count')->label('Créatifs')->counts('users'),
            ])
            ->defaultSort('starts_at', 'desc')
            ->filters([
                SelectFilter::make('brand')->label('Marque')->options(['ub' => 'Ultra-book', 'df' => 'Dustfolio']),
            ])
            ->recordActions([EditAction::make()]);
    }
}
