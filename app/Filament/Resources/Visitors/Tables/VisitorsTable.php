<?php

namespace App\Filament\Resources\Visitors\Tables;

use App\Filament\Support\ActionsCompte;
use App\Filament\Support\FiltrePeriode;
use App\Filament\Support\MenuTri;
use App\Models\Visitor;
use App\Services\Admin\ExportCsv;
use App\Services\Espace\AffichageProfil;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\TextSize as TextColumnSize;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class VisitorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            /*
             | Date de la derniere visite, en sous-requete : c'est par elle
             | qu'on lit cette liste (« qui est encore actif »), et une
             | colonne calculee en PHP ne se trierait pas en base.
             */
            // L'argument doit s'appeler $query : Filament injecte la requete
            // par le nom du parametre, et fabrique sinon un constructeur vide,
            // sans modele, qui casse l'application des filtres.
            ->modifyQueryUsing(fn (Builder $query) => $query->addSelect([
                'derniere_visite' => DB::table('visitor_book_visits')
                    ->selectRaw('max(visited_at)')
                    ->whereColumn('visitor_id', 'visitors.id'),
            ]))
            /*
             | Meme lecture que la liste des creatifs — avatar, identite,
             | marque —, mais les colonnes qui comptent ici sont l'activite :
             | combien de books gardes en memo, combien visites. Un compte
             | visiteur sans memo book ni visite n'a servi a rien.
             */
            ->columns([
                ImageColumn::make('avatar')->label('')->extraCellAttributes(['class' => 'ub-cellule-avatar'])->circular()->imageSize(36)
                    // Un visiteur ne depose pas de photo : c'est toujours le
                    // medaillon d'initiales, aux couleurs des creatifs.
                    ->getStateUsing(fn (Visitor $v) => app(AffichageProfil::class)
                        ->medaillon($v->initiales(), $v->couleur())),

                TextColumn::make('email')->label('Adresse mail')->searchable()->sortable()
                    ->description(fn (Visitor $v) => $v->fullName())
                    ->copyable()
                    ->wrap(),

                // Meme label rouge que chez les creatifs, vide si le compte
                // est actif (voir UsersTable).
                TextColumn::make('blocked_at')->label('État')->badge()->alignCenter()
                    ->formatStateUsing(fn () => __('Bloqué'))
                    ->color('danger')
                    ->placeholder('')
                    ->tooltip(fn (Visitor $v) => $v->estBloque()
                        ? trim(__('Bloqué le :date', ['date' => $v->blocked_at?->format('d/m/Y')]).' — '.($v->blocked_reason ?: '—'))
                        : null),
                TextColumn::make('lastname')->label('Nom')->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('firstname')->label('Prénom')->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('brand')->label('Marque')->badge()->alignCenter()
                    ->formatStateUsing(fn (?string $state) => $state === 'df' ? 'Dustfolio' : 'Ultra-book')
                    ->color(fn (?string $state) => $state === 'df' ? 'warning' : 'info'),
                TextColumn::make('memo_books_count')->label('Memo book')->counts('memoBooks')
                    ->numeric()->sortable()->alignCenter()->size(TextColumnSize::Small),
                TextColumn::make('visites_count')->label('Books visités')->counts('visites')
                    ->numeric()->sortable()->alignCenter()->size(TextColumnSize::Small)->toggleable(),
                TextColumn::make('email_verified_at')->label('Adresse vérifiée')->date('d/m/Y')
                    ->placeholder('Non')->alignCenter()->size(TextColumnSize::Small)
                    ->toggleable(isToggledHiddenByDefault: true),
                /*
                 | Les deux dates qui portent le menu « Trier par ». Comme
                 | chez les creatifs, elles ne sont pas masquables : le tri
                 | natif de la table ignore une colonne cachee.
                 */
                TextColumn::make('derniere_visite')->label('Dernière visite')
                    ->date('d/m/Y')->sortable()->placeholder('—')
                    ->size(TextColumnSize::Small),
                TextColumn::make('created_at')->label('Inscription')->date('d/m/Y')->sortable()
                    ->size(TextColumnSize::Small),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginationPageOptions([25, 50, 100])
            ->defaultPaginationPageOption(50)
            ->filters([
                SelectFilter::make('brand')->label('Marque')
                    ->options(['ub' => 'Ultra-book', 'df' => 'Dustfolio']),
                TernaryFilter::make('bloque')->label('Compte bloqué')
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('blocked_at'),
                        false: fn (Builder $q) => $q->whereNull('blocked_at'),
                    ),
                TernaryFilter::make('memo')->label('A un memo book')
                    ->queries(
                        true: fn (Builder $q) => $q->whereHas('memoBooks'),
                        false: fn (Builder $q) => $q->whereDoesntHave('memoBooks'),
                    ),
                FiltrePeriode::make('inscription_recente', 'Inscrit', 'created_at'),
                TrashedFilter::make()->label('Comptes supprimés'),
            ])
            // Meme barre que chez les creatifs : filtres, recherche, export
            // et organisation des colonnes sur une seule ligne.
            ->filtersLayout(FiltersLayout::Dropdown)
            ->filtersFormWidth(Width::FourExtraLarge)
            ->filtersFormColumns(['default' => 1, 'md' => 2, 'xl' => 3])
            ->recordActions([
                self::blocage(),
                ActionsCompte::priseIdentiteVisiteur(),
                ActionGroup::make([
                    EditAction::make(),
                ]),
            ])
            ->toolbarActions([
                MenuTri::make([
                    'Dernières visites' => ['derniere_visite', 'desc'],
                    'Derniers inscrits' => ['created_at', 'desc'],
                    'Plus gros memo book' => ['memo_books_count', 'desc'],
                ]),
                Action::make('exporter')->label('Exporter en CSV')->icon('heroicon-o-arrow-down-tray')
                    ->action(fn ($livewire) => app(ExportCsv::class)->reponse(
                        $livewire->getFilteredSortedTableQuery()->withCount(['memoBooks', 'visites']),
                        [
                            'Adresse mail' => fn (Visitor $v) => $v->email,
                            'Prénom' => fn (Visitor $v) => $v->firstname,
                            'Nom' => fn (Visitor $v) => $v->lastname,
                            'Marque' => fn (Visitor $v) => $v->brand === 'df' ? 'Dustfolio' : 'Ultra-book',
                            'Memo book' => fn (Visitor $v) => $v->memo_books_count,
                            'Books visités' => fn (Visitor $v) => $v->visites_count,
                            'Adresse vérifiée' => fn (Visitor $v) => $v->email_verified_at?->format('Y-m-d'),
                            'Bloqué' => fn (Visitor $v) => $v->estBloque() ? 'oui' : 'non',
                            'Motif du blocage' => fn (Visitor $v) => $v->blocked_reason,
                            'Inscription' => fn (Visitor $v) => $v->created_at?->format('Y-m-d'),
                        ],
                        'visiteurs',
                    )),
                BulkActionGroup::make([
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Ferme ou rouvre le compte, sans rien effacer — memo book et
     * historique de visites restent en place. Identique au blocage d'un
     * creatif (App\Models\Concerns\PeutEtreBloque).
     */
    private static function blocage(): Action
    {
        return Action::make('blocage')
            ->label(fn (Visitor $v) => $v->estBloque() ? __('Débloquer le compte') : __('Bloquer le compte'))
            ->tooltip(fn (Visitor $v) => $v->estBloque()
                ? __('Compte bloqué le :date', ['date' => $v->blocked_at?->format('d/m/Y')])
                : __('Bloquer le compte'))
            ->icon(fn (Visitor $v) => $v->estBloque() ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
            ->color(fn (Visitor $v) => $v->estBloque() ? 'danger' : 'gray')
            ->iconButton()
            ->modalHeading(fn (Visitor $v) => $v->estBloque()
                ? __('Débloquer :email', ['email' => $v->email])
                : __('Bloquer :email', ['email' => $v->email]))
            ->modalDescription(fn (Visitor $v) => $v->estBloque()
                ? __('Le compte pourra de nouveau se connecter. Rien n’a été effacé pendant le blocage.')
                : __('Le compte ne pourra plus se connecter. Son memo book est conservé.'))
            ->modalSubmitActionLabel(fn (Visitor $v) => $v->estBloque() ? __('Débloquer') : __('Bloquer'))
            ->schema(fn (Visitor $v) => $v->estBloque() ? [] : [
                Textarea::make('motif')->label(__('Motif'))->rows(2)->maxLength(255)
                    ->helperText(__('Note interne : le visiteur ne la voit pas.')),
            ])
            ->action(function (Visitor $v, array $data) {
                if ($v->estBloque()) {
                    $v->debloquer();

                    Notification::make()->title(__('Compte débloqué.'))->success()->send();

                    return;
                }

                $v->bloquer($data['motif'] ?? null);

                Notification::make()->title(__('Compte bloqué.'))->warning()->send();
            });
    }
}
