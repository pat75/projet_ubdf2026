<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Visitor;
use App\Services\Espace\AffichageProfil;
use Carbon\CarbonInterface;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\HtmlString;

/**
 * Les quatre derniers comptes supprimes, createurs et visiteurs reunis,
 * presentes comme la liste des derniers createurs inscrits. Sert de
 * pendant de la courbe des desinscriptions, a sa droite.
 *
 * Suppression veut dire `deleted_at`, comme dans la courbe des
 * desinscriptions : un compte restaure sort de la liste.
 */
class DernieresDesinscriptions extends TableWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Dernières désinscriptions'))
            ->records(fn (): array => $this->lignes())
            ->paginated(false)
            ->emptyStateHeading(__('Aucune désinscription'))
            ->columns([
                ImageColumn::make('avatar')->label('')->extraCellAttributes(['class' => 'ub-cellule-avatar'])->circular()->imageSize(32),

                TextColumn::make('identifiant')->label(__('Compte'))
                    // Identifiant, nom et type sur une seule ligne ; le type
                    // est un label, bleu pour un createur, gris pour un visiteur.
                    ->formatStateUsing(fn (string $state, array $record): HtmlString => new HtmlString(
                        '<span class="ub-creatif"><span class="ub-creatif-login">'.e($state).'</span>'
                        .($record['nom'] !== '' ? '<span class="ub-creatif-nom">'.e($record['nom']).'</span>' : '')
                        .'<span class="ub-label-compte ub-label-'.e($record['famille']).'">'.e($record['type']).'</span>'
                        .'</span>'
                    ))
                    ->html(),

                TextColumn::make('supprime_le')->label(__('Supprimé'))
                    ->formatStateUsing(fn (CarbonInterface $state) => $state->diffForHumans())
                    ->tooltip(fn (array $record) => $record['supprime_le']->format('d/m/Y H:i'))
                    ->alignEnd(),
            ]);
    }

    /**
     * Quatre lignes, cle = « type-id » : un createur et un visiteur peuvent
     * porter le meme numero.
     *
     * @return array<string, array<string, mixed>>
     */
    private function lignes(): array
    {
        $profil = app(AffichageProfil::class);

        $creatifs = User::onlyTrashed()->latest('deleted_at')->limit(4)->get()
            ->map(fn (User $u) => ['cle' => 'creatif-'.$u->id, 'type' => __('Créatif'), 'famille' => 'creatif',
                'identifiant' => $u->login, 'nom' => trim($u->firstname.' '.$u->lastname),
                'avatar' => $profil->photoUrl($u) ?? $profil->medaillonCreatif($u),
                'supprime_le' => $u->deleted_at]);

        $visiteurs = Visitor::onlyTrashed()->latest('deleted_at')->limit(4)->get()
            ->map(fn (Visitor $v) => ['cle' => 'visiteur-'.$v->id, 'type' => __('Visiteur'), 'famille' => 'visiteur',
                'identifiant' => $v->email, 'nom' => $v->fullName(),
                'avatar' => $profil->medaillon($v->initiales(), $v->couleur()),
                'supprime_le' => $v->deleted_at]);

        return $creatifs->concat($visiteurs)
            ->sortByDesc('supprime_le')->take(4)
            ->mapWithKeys(fn (array $ligne) => [$ligne['cle'] => $ligne])
            ->all();
    }
}
