<?php

namespace App\Filament\Pages\Recherche;

use App\Actions\Recherche\LancerAnalyseLot;
use App\Models\Media;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use App\Services\Espace\AffichageProfil;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

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
            $this->actionLot('lancer', 'Analyser les '.LancerAnalyseLot::TAILLE.' images suivantes',
                'Books sélectionnés ou en formule payante active, avec l’accord du créatif. Les plus récemment sélectionnés d’abord.'),
            $this->actionLot('lancerBooks', 'Analyser '.LancerAnalyseLot::BOOKS.' books entiers',
                'Toutes les images restantes des '.LancerAnalyseLot::BOOKS.' prochains books. Compter ~5 s par image.',
                LancerAnalyseLot::BOOKS),
        ];
    }

    private function actionLot(string $nom, string $libelle, string $aide, ?int $books = null): Action
    {
        return Action::make($nom)
            ->label($libelle)
            ->icon('heroicon-o-sparkles')
            ->tooltip($aide)
            ->action(function () use ($books) {
                ['ok' => $ok, 'erreurs' => $erreurs] = app(LancerAnalyseLot::class)(books: $books, avant: fn (Media $media, int $rang, int $total) => $this->stream(
                    content: e("{$rang}/{$total} · {$media->user->login} · {$media->filename}"),
                    replace: true,
                    name: 'analyse-en-cours',
                ));

                Notification::make()
                    ->title($ok + $erreurs === 0
                        ? 'Aucune image à analyser'
                        : $ok.' image'.($ok > 1 ? 's' : '').' analysée'.($ok > 1 ? 's' : ''))
                    ->body($erreurs ? $erreurs.' en erreur (voir le journal)' : null)
                    ->{$erreurs || ! $ok ? 'warning' : 'success'}()
                    ->send();
            });
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
                // Lien « Mots-clés » en tête de ligne, 50px avant l'avatar.
                TextColumn::make('lien_motcles')->label('')->state('Mots-clés')
                    ->icon('heroicon-o-tag')->color('primary')
                    ->extraCellAttributes(['class' => 'ub-cellule-motscles'])
                    ->action(
                        Action::make('detail')->label('Mots-clés')->icon('heroicon-o-tag')
                            ->modalHeading(fn (User $record) => 'Mots-clés de '.$record->login)
                            ->modalWidth('5xl')
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Fermer')
                            ->modalContent(fn (User $record) => view('filament.recherche.detail-motcles', [
                                'medias' => $record->media()->whereNotNull('analysed_at')->with('tags')->latest('analysed_at')->get(),
                            ]))
                    ),
                // Vignette et ligne « login nom » de la liste des créatifs, 10px entre les deux.
                ImageColumn::make('avatar')->label('')->extraCellAttributes(['class' => 'ub-cellule-avatar ub-cellule-avatar-10'])->circular()->imageSize(36)
                    ->getStateUsing(fn (User $record) => app(AffichageProfil::class)->photoUrl($record))
                    ->defaultImageUrl(fn (User $record) => app(AffichageProfil::class)->medaillonCreatif($record)),
                TextColumn::make('login')->label('Créatif')->searchable(['login', 'firstname', 'lastname'])
                    ->formatStateUsing(fn (User $record): HtmlString => new HtmlString(
                        '<span class="ub-creatif"><span class="ub-creatif-login">'.e($record->login).'</span>'
                        .(($nom = trim($record->firstname.' '.$record->lastname)) !== '' ? '<span class="ub-creatif-nom">'.e($nom).'</span>' : '')
                        .'</span>'
                    ))
                    ->html(),
                TextColumn::make('nb_images')->label('Images analysées')->alignCenter()->sortable(),
                TextColumn::make('nb_motcles')->label('Mots-clés')->alignCenter()->sortable(),
                TextColumn::make('derniere_analyse')->label('Dernière analyse')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('derniere_analyse', 'desc')
            ->recordActions([
                // Les mots-clés seuls : l'image reste marquée analysée, une
                // prochaine passe ne les remet donc pas.
                Action::make('retirerMotsCles')->label('Retirer les mots-clés')->icon('heroicon-o-trash')->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record) => 'Retirer tous les mots-clés de '.$record->login.' ?')
                    ->modalDescription('Les mots-clés de toutes ses images analysées seront supprimés.')
                    ->action(function (User $record) {
                        $retires = DB::table('media_tag')
                            ->whereIn('media_id', $record->media()->withTrashed()->select('id'))
                            ->delete();

                        Notification::make()->title($retires.' mot'.($retires > 1 ? 's' : '').'-clé'.($retires > 1 ? 's' : '').' retiré'.($retires > 1 ? 's' : ''))->success()->send();
                    }),
            ]);
    }
}
