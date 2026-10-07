<?php

namespace App\Filament\Pages\Recherche;

use App\Models\SearchQuery;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** Journal des recherches du portail : mots-cles, creatifs renvoyes, recherches vaines. */
class DernieresRecherches extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static ?string $navigationLabel = 'Dernières recherches';

    protected static ?string $title = 'Dernières recherches';

    protected static ?string $slug = 'recherche/dernieres';

    protected static string|\UnitEnum|null $navigationGroup = 'Recherche';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.recherche.page-table';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => SearchQuery::query())
            ->columns([
                TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('q')->label('Mots-clés')->weight('bold')->searchable(),
                TextColumn::make('result_user_ids')->label('Créatifs renvoyés')->wrap()
                    ->state(fn (SearchQuery $record) => $this->logins($record->result_user_ids ?? []))
                    ->placeholder('—'),
                TextColumn::make('nb_books')->label('Books')->alignCenter()->sortable()->placeholder('?'),
                TextColumn::make('nb_images')->label('Images')->alignCenter()->sortable()->placeholder('?'),
                TextColumn::make('brand')->label('Marque')->badge()
                    ->formatStateUsing(fn (?string $state) => $state === 'df' ? 'Dustfolio' : 'Ultra-book')
                    ->color(fn (?string $state) => $state === 'df' ? 'warning' : 'info'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(50)
            ->filters([
                // Les lignes d'avant le journal enrichi (compteurs nuls) ne comptent pas.
                Filter::make('sans_resultat')->label('Sans résultat')
                    ->query(fn (Builder $query) => $query->where('nb_books', 0)->where('nb_images', 0)),
                SelectFilter::make('brand')->label('Marque')
                    ->options(['ub' => 'Ultra-book', 'df' => 'Dustfolio']),
            ]);
    }

    /** @var array<int, string> logins deja lus, une requete pour toute la page */
    private array $logins = [];

    /** @param  list<int>  $ids */
    private function logins(array $ids): ?string
    {
        $manquants = array_diff($ids, array_keys($this->logins));
        if ($manquants) {
            $this->logins += User::whereIn('id', $manquants)->pluck('login', 'id')->all();
        }

        $logins = array_filter(array_map(fn ($id) => $this->logins[$id] ?? null, $ids));

        return $logins ? implode(', ', $logins) : null;
    }
}
