<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Services\Admin\ExportCsv;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('login')->label('Identifiant')->searchable()->sortable()
                    ->description(fn (User $u) => trim($u->firstname.' '.$u->lastname) ?: null)
                    ->url(fn (User $u) => $u->bookUrl(), shouldOpenInNewTab: true),
                TextColumn::make('email')->label('E-mail')->searchable()->toggleable(),
                TextColumn::make('category.name')->label('Métier')->sortable()->toggleable(),
                TextColumn::make('brand')->label('Marque')->badge()
                    ->formatStateUsing(fn (?string $state) => $state === 'df' ? 'Dustfolio' : 'Ultra-book'),
                TextColumn::make('plan')->label('Formule')->badge()
                    ->formatStateUsing(fn (?int $state) => $state ? 'Payante' : 'Gratuite')
                    ->color(fn (?int $state) => $state ? 'success' : 'gray')
                    ->description(fn (User $u) => $u->echeanceFormule()?->format('d/m/Y')),
                IconColumn::make('bookSetting.diffuse_web')->label('En ligne')->boolean()->toggleable(),
                TextColumn::make('media_count')->label('Visuels')->numeric()->sortable()->toggleable(),
                TextColumn::make('created_at')->label('Inscription')->date('d/m/Y')->sortable()->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('brand')->label('Marque')
                    ->options(['ub' => 'Ultra-book', 'df' => 'Dustfolio']),
                SelectFilter::make('category_id')->label('Métier')->relationship('category', 'name')->searchable(),
                TernaryFilter::make('plan')->label('Formule payante')
                    ->queries(
                        true: fn (Builder $q) => $q->where('plan', '>', 0),
                        false: fn (Builder $q) => $q->where('plan', 0),
                    ),
                Filter::make('echue')->label('Formule échue')
                    ->query(fn (Builder $q) => $q->where('plan', '>', 0)->where('plan_expires_at', '<', now())),
                TrashedFilter::make()->label('Comptes supprimés'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('book')->label('Voir le book')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (User $u) => $u->bookUrl(), shouldOpenInNewTab: true),
            ])
            ->headerActions([
                Action::make('exporter')->label('Exporter en CSV')->icon('heroicon-o-arrow-down-tray')
                    // La requete du tableau : l'export suit la recherche et les filtres affiches.
                    ->action(fn ($livewire) => app(ExportCsv::class)->reponse(
                        $livewire->getFilteredSortedTableQuery()->with(['category', 'bookSetting']),
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
