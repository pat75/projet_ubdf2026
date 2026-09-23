<?php

namespace App\Filament\Resources\Campaigns\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nom')->searchable(),
                TextColumn::make('subject')->label('Objet')->limit(50)->toggleable(),
                TextColumn::make('brand')->label('Marque')->badge()
                    ->formatStateUsing(fn (?string $state) => $state === 'df' ? 'Dustfolio' : 'Ultra-book'),
                TextColumn::make('type')->label('Type')->badge()->toggleable(),
                TextColumn::make('sends_count')->label('Envois')->counts('sends'),
                TextColumn::make('scheduled_at')->label('Programmée')->dateTime('d/m/Y H:i')->placeholder('—'),
                TextColumn::make('sent_at')->label('Envoyée')->dateTime('d/m/Y H:i')->placeholder('—')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('brand')->label('Marque')->options(['ub' => 'Ultra-book', 'df' => 'Dustfolio']),
            ])
            ->recordActions([EditAction::make()]);
    }
}
