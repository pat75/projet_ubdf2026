<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\Paiement\Relances;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

/**
 * Relance manuelle des abonnements echus (abo_relance_liste*.php du
 * legacy). Les relances J-5 et jour J partent seules chaque jour
 * (ubdf:relancer-formules) ; celle-ci rattrape les comptes dont la formule
 * est deja passee : au plus une relance tous les 7 jours, tracee dans
 * `subscription_reminders`. En face du titre, le nombre d'abonnements
 * repris apres relance.
 */
class RelanceAbonnementEchu extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Relance abonnement échu';

    protected static ?string $title = 'Relance abonnement échu';

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.relance-abonnement-echu';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => User::query()
                ->where('plan', '>', 0)
                ->where('plan_expires_at', '<', now())
                ->withCount(['relancesFormule as nb_relances' => fn (Builder $query) => $query
                    ->where('days_before', Relances::JALON_ECHU)
                    ->whereColumn('expires_on', 'users.plan_expires_at')])
                ->withMax(['relancesFormule as derniere_relance' => fn (Builder $query) => $query
                    ->where('days_before', Relances::JALON_ECHU)], 'sent_at')
                ->withCasts(['derniere_relance' => 'datetime']))
            ->columns([
                TextColumn::make('login')->label('Créatif')->searchable(['login', 'email'])
                    ->formatStateUsing(fn (User $u) => new HtmlString('<strong>'.e($u->login).'</strong> '.e($u->email)))
                    ->wrap(false),
                TextColumn::make('brand')->label('Marque')->badge()
                    ->formatStateUsing(fn (?string $state) => $state === 'df' ? 'Dustfolio' : 'Ultra-book')
                    ->color(fn (?string $state) => $state === 'df' ? 'warning' : 'info'),
                TextColumn::make('plan_expires_at')->label('Échue le')->date('d/m/Y')->sortable(),
                TextColumn::make('nb_relances')->label('Relances')->alignCenter()->sortable(),
                TextColumn::make('derniere_relance')->label('Dernière relance')->dateTime('d/m/Y H:i')->placeholder('—')->sortable(),
            ])
            ->defaultSort('plan_expires_at', 'desc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(50)
            ->filters([
                Filter::make('relancables')->label('Relançables (aucune relance depuis '.Relances::DELAI_RELANCE_ECHU.' jours)')->default()
                    ->query(fn (Builder $query) => $query->whereDoesntHave('relancesFormule', fn (Builder $query) => $query
                        ->where('days_before', Relances::JALON_ECHU)
                        ->where('sent_at', '>', now()->subDays(Relances::DELAI_RELANCE_ECHU)))),
                SelectFilter::make('brand')->label('Marque')
                    ->options(['ub' => 'Ultra-book', 'df' => 'Dustfolio']),
            ])
            ->recordActions([
                Action::make('relancer')->label('Relancer')->icon('heroicon-o-envelope')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $this->notifier(app(Relances::class)->relancerEchu($record) ? 1 : 0, 1)),
            ])
            ->toolbarActions([
                BulkAction::make('relancer')->label('Relancer la sélection')->icon('heroicon-o-envelope')
                    ->requiresConfirmation()
                    ->modalDescription('Un message par compte : ceux relancés depuis moins de '.Relances::DELAI_RELANCE_ECHU.' jours sont ignorés.')
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records) {
                        $relances = app(Relances::class);

                        $this->notifier($records->filter(fn (User $u) => $relances->relancerEchu($u))->count(), $records->count());
                    }),
            ]);
    }

    /** Badge noir de la charte, en face du titre : abonnements repris apres relance. */
    protected function getHeaderActions(): array
    {
        $reprises = app(Relances::class)->reprises();

        return [
            Action::make('reprises')
                ->label($reprises.' abonnement'.($reprises > 1 ? 's' : '').' repris')
                ->disabled()
                ->extraAttributes(['style' => 'background:#000;color:#fff;border-radius:4px;font-weight:700;font-size:14px;padding:.625rem 1.125rem;opacity:1;cursor:default']),
        ];
    }

    private function notifier(int $envoyes, int $total): void
    {
        Notification::make()
            ->title($envoyes.' relance'.($envoyes > 1 ? 's' : '').' envoyée'.($envoyes > 1 ? 's' : '').($envoyes < $total ? ' ('.($total - $envoyes).' ignorée'.($total - $envoyes > 1 ? 's' : '').' : relancée récemment ou sans adresse)' : ''))
            ->{$envoyes ? 'success' : 'warning'}()
            ->send();
    }
}
