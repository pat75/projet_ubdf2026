<?php

namespace App\Filament\Resources\Actualites\Tables;

use App\Models\Actualite;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActualitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ordre')->label('Ordre')->sortable(),
                TextColumn::make('titre')->label('Titre')->searchable()->limit(60),
                TextColumn::make('emplacement')
                    ->label('Affichage')
                    ->formatStateUsing(fn (?string $state): string => Actualite::EMPLACEMENTS[$state] ?? (string) $state),
                IconColumn::make('actif')->label('Active')->boolean(),
                TextColumn::make('updated_at')->label('Modifiée')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('ordre')
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
