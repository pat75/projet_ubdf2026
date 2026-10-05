<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Support\ActionsCompte;
use App\Models\User;
use App\Services\Espace\AffichageProfil;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\HtmlString;

/**
 * Les dix derniers comptes createurs ouverts, a cote de ceux des
 * visiteurs.
 *
 * Deux gestes seulement, les memes que dans la liste complete : entrer
 * dans le compte, et mettre le book en page d'accueil — c'est ce qu'on
 * fait d'une inscription du jour. Tout le reste est a un clic, par le
 * lien vers la liste.
 */
class DernieresInscriptionsCreatifs extends TableWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Derniers créatifs inscrits'))
            ->headerActions([
                Action::make('tous')
                    ->label(__('Voir tous les créatifs'))
                    ->icon('heroicon-o-arrow-right')
                    ->link()
                    ->url(UserResource::getUrl()),
            ])
            // Dix lignes : de quoi voir la journee sans faire descendre
            // les graphiques hors de l'ecran.
            ->query(User::query()->latest('created_at')->limit(10))
            ->paginated(false)
            ->columns([
                // Avatar, identifiant et nom dans une seule cellule, sur une
                // ligne : deux colonnes laissaient un blanc entre la photo
                // et le nom.
                TextColumn::make('login')->label(__('Créatif'))
                    ->formatStateUsing(function (User $u): HtmlString {
                        $profil = app(AffichageProfil::class);
                        $photo = $profil->photoUrl($u) ?? $profil->medaillonCreatif($u);
                        $nom = trim($u->firstname.' '.$u->lastname);

                        return new HtmlString('<span class="ub-creatif">'
                            .'<img src="'.e($photo).'" alt="" class="ub-creatif-avatar">'
                            .'<span class="ub-creatif-login">'.e($u->login).'</span>'
                            .($nom !== '' ? '<span class="ub-creatif-nom">'.e($nom).'</span>' : '')
                            .'</span>');
                    })
                    ->html()
                    ->url(fn (User $u) => $u->bookUrl(), shouldOpenInNewTab: true),

                TextColumn::make('created_at')->label(__('Inscrit'))
                    ->since()->tooltip(fn (User $u) => $u->created_at?->format('d/m/Y H:i'))
                    ->alignEnd(),
            ])
            ->recordActions([
                ActionsCompte::selection(),
                ActionsCompte::priseIdentiteCreatif(),
            ]);
    }
}
