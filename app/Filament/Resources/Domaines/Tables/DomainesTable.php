<?php

namespace App\Filament\Resources\Domaines\Tables;

use App\Models\Domaine;
use App\Services\Espace\AffichageProfil;
use Filament\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Noms de domaine commandes : createur, dates d'achat, d'echeance et de
 * resiliation, relances envoyees. Comme la liste des createurs, le bouton
 * « se connecter en tant que » precede l'avatar.
 */
class DomainesTable
{
    private const STATUTS = [
        'a_payer' => 'À payer', 'paye' => 'Payé', 'commande' => 'Commandé', 'echec_commande' => 'Échec commande',
        'rembourse' => 'Remboursé', 'en_attente' => 'DNS en attente', 'dns_ok' => 'DNS ok', 'actif' => 'Actif',
        'suspendu' => 'Suspendu', 'resilie' => 'Résilié', 'transfert' => 'Transfert', 'expire' => 'Expiré',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('user'))
            ->defaultSort('id', 'desc')
            ->columns([
                ImageColumn::make('avatar')->label('')->circular()->imageSize(36)
                    ->getStateUsing(fn (Domaine $record) => $record->user ? app(AffichageProfil::class)->photoUrl($record->user) : null)
                    ->defaultImageUrl(fn (Domaine $record) => $record->user ? app(AffichageProfil::class)->medaillonCreatif($record->user) : null),
                TextColumn::make('user.login')->label('Créatif')->searchable()
                    ->description(fn (Domaine $record) => $record->user?->fullName()),
                TextColumn::make('nom')->label('Domaine')->searchable()->sortable()->weight('bold')
                    ->url(fn (Domaine $record) => 'https://'.$record->nom, shouldOpenInNewTab: true),
                TextColumn::make('statut')->label('Statut')->badge()
                    ->formatStateUsing(fn (string $state) => self::STATUTS[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'actif' => 'success',
                        'echec_commande', 'rembourse', 'suspendu', 'expire' => 'danger',
                        'resilie', 'transfert' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('prix_ttc')->label('Prix TTC')->money('EUR')->sortable(),
                TextColumn::make('reserve_le')->label('Acheté le')->date('d/m/Y')->sortable()->placeholder('—'),
                TextColumn::make('expire_le')->label('Échéance')->date('d/m/Y')->sortable()->placeholder('—'),
                TextColumn::make('resilie_le')->label('Résilié le')->date('d/m/Y')->sortable()->placeholder('—'),
                TextColumn::make('relances_envoyees')->label('Relances')->sortable()
                    ->formatStateUsing(fn (int $state) => $state.' / '.count(Domaine::RELANCES))
                    ->description(fn (Domaine $record) => $record->derniere_relance_le ? 'dernière le '.$record->derniere_relance_le->format('d/m/Y') : null),
                TextColumn::make('ovh_commande_id')->label('Commande OVH')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('statut')->label('Statut')->options(self::STATUTS),
            ])
            ->recordActions(position: RecordActionsPosition::BeforeColumns, actions: [
                // Meme mecanique que ActionsCompte::priseIdentiteCreatif, sur le createur du domaine.
                Action::make('prise_identite')
                    ->label(__('Se connecter en tant que'))
                    ->tooltip(fn (Domaine $record) => __('Ouvrir l’espace de :login', ['login' => $record->user?->login]))
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('gray')
                    ->iconButton()
                    ->visible(fn (Domaine $record) => $record->user !== null)
                    ->action(fn (Domaine $record) => redirect()->route('admin.prise-identite.relais', ['creatif' => $record->user])),
            ]);
    }
}
