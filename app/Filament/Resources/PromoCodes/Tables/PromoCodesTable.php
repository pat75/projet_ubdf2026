<?php

namespace App\Filament\Resources\PromoCodes\Tables;

use App\Models\PromoCode;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PromoCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Code')->searchable()->copyable(),
                TextColumn::make('discount')->label('Mois offerts')->numeric()->sortable()
                    ->visible(fn () => true),
                TextColumn::make('uses')->label('Utilisations')
                    ->formatStateUsing(fn (PromoCode $c) => $c->uses.' / '.($c->max_uses ?? '∞')),
                IconColumn::make('is_active')->label('Actif')->boolean(),
                TextColumn::make('ends_at')->label('Expire le')->date('d/m/Y')->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->label('Créé le')->date('d/m/Y')->sortable()->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_active')->label('Actif'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
