<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\Stats\VisitesAjoutees;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Visites ajoutees aux books de moins de VisitesAjoutees::PLAFOND vues :
 * lancement manuel d'un lot, et suivi des books concernes.
 */
class Statistiques extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Statistiques';

    protected static ?string $title = 'Statistiques';

    protected static string|\UnitEnum|null $navigationGroup = 'Créatifs';

    protected string $view = 'filament.pages.relance-abonnement-echu';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => User::query()
                ->whereHas('visitesAjoutees')
                ->withSum('visitStats', 'public_views')
                ->withSum(['visitesAjoutees as ajoutees' => fn (Builder $query) => $query->whereNotNull('appliquee_at')], 'nb')
                ->withSum(['visitesAjoutees as prevues' => fn (Builder $query) => $query->whereNull('appliquee_at')], 'nb')
                ->withMax('visitesAjoutees as dernier_ajout', 'appliquee_at')
                ->withCasts(['dernier_ajout' => 'datetime']))
            ->columns([
                TextColumn::make('login')->label('Créatif')->searchable(['login', 'email'])->weight('bold'),
                TextColumn::make('reelles')->label('Visites existantes')->alignEnd()
                    ->state(fn (User $record) => number_format($record->vuesTotales() - (int) $record->ajoutees, 0, ',', ' ')),
                TextColumn::make('ajoutees')->label('Ajoutées')->alignEnd()->default(0)->sortable(),
                TextColumn::make('prevues')->label('À venir')->alignEnd()->default(0)->sortable(),
                TextColumn::make('total')->label('Total affiché')->alignEnd()
                    ->state(fn (User $record) => number_format($record->vuesTotales(), 0, ',', ' ')),
                TextColumn::make('dernier_ajout')->label('Dernier ajout')->dateTime('d/m/Y H:i')->placeholder('—')->sortable(),
            ])
            ->defaultSort('dernier_ajout', 'desc')
            ->headerActions([
                Action::make('lot')
                    ->label('Ajouter des visites à 10 portfolios')
                    ->icon(Heroicon::OutlinedPlus)
                    ->schema([
                        TextInput::make('books')->label('Portfolios')->integer()->minValue(1)->maxValue(100)->default(10)->required(),
                        TextInput::make('jours')->label('Durée (jours)')->integer()->minValue(1)->maxValue(90)->default(30)->required(),
                    ])
                    ->action(function (array $data) {
                        $nb = app(VisitesAjoutees::class)->creerLot((int) $data['books'], (int) $data['jours']);
                        Notification::make()->title($nb.' portfolio(s) ajouté(s) au lot')->success()->send();
                    }),
            ])
            ->recordActions([
                Action::make('retirer')
                    ->label('Retirer')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Les visites ajoutées sont retirées des statistiques, les visites à venir annulées.')
                    ->action(fn (User $record) => app(VisitesAjoutees::class)->retirer($record)),
            ]);
    }
}
