<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Services\Admin\ExportCsv;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Enums\TextSize as TextColumnSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            /*
             | Colonnes calibrees pour tenir sur un ecran de 1024 px : six
             | colonnes visibles, le reste en option dans le menu
             | « Colonnes ». L'e-mail reste cherchable meme masque.
             */
            ->columns([
                TextColumn::make('login')->label('Identifiant')->searchable()->sortable()
                    ->description(fn (User $u) => trim($u->firstname.' '.$u->lastname) ?: null)
                    ->url(fn (User $u) => $u->bookUrl(), shouldOpenInNewTab: true)
                    ->wrap(),
                TextColumn::make('category.name')->label('Métier')->sortable()->toggleable()
                    ->size(TextColumnSize::Small)
                    // Les intitules de metier sont longs ; on les tronque
                    // plutot que de laisser le tableau deborder.
                    ->limit(18)->tooltip(fn (User $u) => $u->category?->name),
                TextColumn::make('brand')->label('Marque')->badge()->alignCenter()
                    ->formatStateUsing(fn (?string $state) => $state === 'df' ? 'Dustfolio' : 'Ultra-book')
                    ->color(fn (?string $state) => $state === 'df' ? 'warning' : 'info'),
                TextColumn::make('plan')->label('Formule')->badge()->alignCenter()
                    ->formatStateUsing(fn (?int $state) => $state ? 'Payante' : 'Gratuite')
                    ->color(fn (?int $state) => $state ? 'success' : 'gray')
                    ->description(fn (User $u) => $u->echeanceFormule()?->format('d/m/Y'), position: 'below'),
                IconColumn::make('bookSetting.diffuse_web')->label('En ligne')->boolean()
                    ->alignCenter()->toggleable(),
                IconColumn::make('billingProfile.siret')->label('Pro')->boolean()
                    ->tooltip(fn (User $u) => $u->billingProfile?->company_name)
                    ->alignCenter()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('media_count')->label('Visuels')->numeric()->sortable()
                    ->alignCenter()->size(TextColumnSize::Small)->toggleable(),

                // Masquees par defaut : a rappeler via le menu « Colonnes ».
                TextColumn::make('email')->label('E-mail')->searchable()
                    ->size(TextColumnSize::Small)->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('city')->label('Ville')->searchable()
                    ->size(TextColumnSize::Small)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Inscription')->date('d/m/Y')->sortable()
                    ->size(TextColumnSize::Small)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginationPageOptions([25, 50, 100])
            ->defaultPaginationPageOption(50)
            ->filters([
                SelectFilter::make('brand')->label('Marque')
                    ->options(['ub' => 'Ultra-book', 'df' => 'Dustfolio']),
                SelectFilter::make('category_id')->label('Métier')->relationship('category', 'name')->searchable(),
                TernaryFilter::make('plan')->label('Formule payante')
                    ->queries(
                        true: fn (Builder $q) => $q->where('plan', '>', 0),
                        false: fn (Builder $q) => $q->where('plan', 0),
                    ),
                TernaryFilter::make('facturation')->label('Facturation électronique')
                    ->queries(
                        true: fn (Builder $q) => $q->whereHas('billingProfile'),
                        false: fn (Builder $q) => $q->whereDoesntHave('billingProfile'),
                    ),
                Filter::make('echue')->label('Formule échue')
                    ->query(fn (Builder $q) => $q->where('plan', '>', 0)->where('plan_expires_at', '<', now())),
                TrashedFilter::make()->label('Comptes supprimés'),
            ])
            // Depliables au-dessus du tableau, sur trois colonnes : le menu
            // deroulant par defaut est etroit et illisible sur tablette.
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(['default' => 1, 'md' => 2, 'xl' => 3])
            // Repliees derriere un bouton unique : deux colonnes d'actions
            // cotoyant six colonnes de donnees ne tiennent pas en 1024 px.
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('book')->label('Voir le book')->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (User $u) => $u->bookUrl(), shouldOpenInNewTab: true),
                ]),
            ])
            ->headerActions([
                Action::make('exporter')->label('Exporter en CSV')->icon('heroicon-o-arrow-down-tray')
                    // La requete du tableau : l'export suit la recherche et les filtres affiches.
                    ->action(fn ($livewire) => app(ExportCsv::class)->reponse(
                        $livewire->getFilteredSortedTableQuery()->with(['category', 'bookSetting', 'billingProfile']),
                        [
                            'Identifiant' => fn (User $u) => $u->login,
                            'Nom' => fn (User $u) => $u->fullName(),
                            'E-mail' => fn (User $u) => $u->email,
                            'Métier' => fn (User $u) => $u->category?->name,
                            'Marque' => fn (User $u) => $u->brand === 'df' ? 'Dustfolio' : 'Ultra-book',
                            'Ville' => fn (User $u) => $u->city,
                            'Pays' => fn (User $u) => $u->country,
                            'Formule' => fn (User $u) => $u->plan ? 'Payante' : 'Gratuite',
                            'Échéance' => fn (User $u) => $u->echeanceFormule()?->format('Y-m-d'),
                            'Book en ligne' => fn (User $u) => $u->bookSetting?->diffuse_web ? 'oui' : 'non',
                            'Newsletter' => fn (User $u) => $u->bookSetting?->diffuse_newsletter ? 'oui' : 'non',
                            'Visuels' => fn (User $u) => $u->media_count,
                            'SIRET' => fn (User $u) => $u->billingProfile?->siret,
                            'Raison sociale' => fn (User $u) => $u->billingProfile?->company_name,
                            'TVA' => fn (User $u) => $u->billingProfile?->vat_number,
                            'Inscription' => fn (User $u) => $u->created_at?->format('Y-m-d'),
                        ],
                        'creatifs',
                    )),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
