<?php

namespace App\Filament\Pages\Recherche;

use App\Actions\Recherche\LancerAnalyseLot;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Analyse IA des visuels : lancement manuel par lots, statistiques, et
 * creatifs deja analyses avec le detail de leurs mots-cles.
 */
class AnalyseIA extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Analyse IA des images';

    protected static ?string $title = 'Analyse IA des images';

    protected static ?string $slug = 'recherche/analyse-ia';

    protected static string|\UnitEnum|null $navigationGroup = 'Recherche';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.recherche.page-table';

    protected function getHeaderWidgets(): array
    {
        return [StatistiquesAnalyse::class, ProgressionMotsCles::class];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('lancer')
                ->label('Analyser les '.LancerAnalyseLot::TAILLE.' images suivantes')
                ->icon('heroicon-o-sparkles')
                ->requiresConfirmation()
                ->modalDescription('Books sélectionnés ou en formule payante active, dont le créatif a donné son accord. Les plus récemment sélectionnés passent en premier.')
                ->action(function () {
                    $n = app(LancerAnalyseLot::class)();

                    Notification::make()
                        ->title($n ? $n.' image'.($n > 1 ? 's' : '').' en cours d’analyse' : 'Aucune image à analyser')
                        ->body($n ? 'Les résultats arrivent au fil de la file d’attente : rechargez la page dans une minute.' : null)
                        ->{$n ? 'success' : 'warning'}()
                        ->send();
                }),
        ];
    }

    public function table(Table $table): Table
    {
        $analyses = fn (Builder $query) => $query->whereNotNull('analysed_at');

        return $table
            ->query(fn (): Builder => User::query()
                ->whereHas('media', $analyses)
                ->withCount(['media as nb_images' => $analyses])
                ->withMax(['media as derniere_analyse' => $analyses], 'analysed_at')
                ->withCasts(['derniere_analyse' => 'datetime'])
                ->addSelect(['nb_motcles' => DB::table('media_tag')
                    ->join('media', 'media.id', '=', 'media_tag.media_id')
                    ->whereColumn('media.user_id', 'users.id')
                    ->selectRaw('COUNT(DISTINCT media_tag.tag_id)')]))
            ->columns([
                TextColumn::make('login')->label('Créatif')->searchable(['login', 'firstname', 'lastname'])
                    ->description(fn (User $record) => $record->fullName()),
                TextColumn::make('nb_images')->label('Images analysées')->alignCenter()->sortable(),
                TextColumn::make('nb_motcles')->label('Mots-clés')->alignCenter()->sortable(),
                TextColumn::make('derniere_analyse')->label('Dernière analyse')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('derniere_analyse', 'desc')
            ->recordActions([
                Action::make('detail')->label('Mots-clés')->icon('heroicon-o-tag')
                    ->modalHeading(fn (User $record) => 'Mots-clés de '.$record->login)
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer')
                    ->modalContent(fn (User $record) => view('filament.recherche.detail-motcles', [
                        'medias' => $record->media()->whereNotNull('analysed_at')->with('tags')->latest('analysed_at')->get(),
                    ])),
            ]);
    }
}
