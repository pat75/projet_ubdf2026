<?php

namespace App\Filament\Resources\PromoCodes\Tables;

use App\Models\PromoCode;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                IconColumn::make('utilise')->label('Utilisé')->boolean()
                    ->getStateUsing(fn (PromoCode $c) => $c->uses > 0),
                IconColumn::make('is_active')->label('Actif')->boolean(),
                TextColumn::make('ends_at')->label('Expire le')->date('d/m/Y')->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->label('Créé le')->date('d/m/Y')->sortable()->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            // Code deja utilise : ligne hachuree (voir styles.blade.php).
            ->recordClasses(fn (PromoCode $record) => $record->uses > 0 ? 'ub-code-utilise' : null)
            ->filters([
                TernaryFilter::make('utilise')->label('Utilisation')
                    ->placeholder('Tous les codes')
                    ->trueLabel('Codes utilisés')
                    ->falseLabel('Codes non utilisés')
                    ->queries(
                        true: fn (Builder $query) => $query->where('uses', '>', 0),
                        false: fn (Builder $query) => $query->where('uses', '<=', 0),
                        blank: fn (Builder $query) => $query,
                    ),
                TernaryFilter::make('is_active')->label('Actif'),
            ])
            ->recordActions([EditAction::make()])
            // Directement dans la barre, sans menu « Actions groupees » :
            // elle apparait des qu'une ligne est cochee.
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->label('Supprimer la sélection')
                    ->modalHeading('Supprimer les codes sélectionnés ?')
                    ->modalDescription('Cette action est définitive.')
                    ->successNotificationTitle('Codes supprimés'),
            ]);
    }
}
