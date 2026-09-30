<?php

namespace App\Filament\Support;

use App\Models\User;
use App\Models\Visitor;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

/**
 * Les gestes qu'on refait sur un compte partout ou il apparait : la liste
 * des creatifs, celle des visiteurs, et les dernieres inscriptions du
 * tableau de bord.
 *
 * Rassembles ici plutot que recopies dans chaque tableau : l'icone, le
 * libelle et la notification doivent rester les memes d'un ecran a
 * l'autre, sans quoi la meme action parait differente selon l'endroit.
 */
class ActionsCompte
{
    /**
     * Met le book en page d'accueil, ou l'en retire. Sans fenetre de
     * confirmation : le geste se defait du meme clic.
     *
     * La date suit toute seule — le modele la pose en entrant en selection
     * et l'efface en sortant (App\Models\User::booted).
     */
    public static function selection(): Action
    {
        return Action::make('selection')
            ->label(fn (User $u) => $u->in_home_selection ? __('Retirer de la sélection') : __('Sélectionner'))
            ->tooltip(fn (User $u) => $u->in_home_selection ? __('Retirer de la sélection') : __('Sélectionner'))
            ->icon(fn (User $u) => $u->in_home_selection ? 'heroicon-s-star' : 'heroicon-o-star')
            ->color(fn (User $u) => $u->in_home_selection ? 'warning' : 'gray')
            ->iconButton()
            ->action(function (User $u) {
                $u->update(['in_home_selection' => ! $u->in_home_selection]);

                Notification::make()
                    ->title($u->in_home_selection
                        ? __(':login est en sélection.', ['login' => $u->login])
                        : __(':login n’est plus en sélection.', ['login' => $u->login]))
                    ->success()->send();
            });
    }

    /**
     * Ouvre l'espace du creatif sous son identite, sans quitter le
     * back-office.
     *
     * Les deux gardes cohabitent : la session `admin` reste ouverte et
     * c'est elle qui autorise le retour (App\Http\Controllers\Admin\
     * PriseIdentiteController), qui journalise au passage la prise
     * d'identite dans les activites admin.
     *
     * Sans fenetre de confirmation : le geste est immediat, et il ne
     * detruit rien — le bandeau rouge de l'espace dit sous quelle identite
     * on se trouve, et rend la main d'un clic. Un compte bloque s'ouvre
     * aussi (RefuserComptesBloques exempte la prise d'identite).
     *
     * Un GET serait rejouable depuis l'historique du navigateur : on passe
     * par une page de relais qui poste le formulaire d'elle-meme.
     */
    public static function priseIdentiteCreatif(): Action
    {
        return Action::make('prise_identite')
            ->label(__('Se connecter en tant que'))
            ->tooltip(fn (User $u) => __('Ouvrir l’espace de :login', ['login' => $u->login]))
            ->icon('heroicon-o-arrow-right-on-rectangle')
            ->color('gray')
            ->iconButton()
            ->action(fn (User $u) => redirect()->route('admin.prise-identite.relais', ['creatif' => $u]));
    }

    /**
     * Meme mecanique pour un visiteur, sur la garde `visitor`
     * (App\Http\Controllers\Admin\PriseIdentiteVisiteurController).
     *
     * Utile surtout pour voir son memo book tel qu'il le voit : c'est la
     * seule chose qu'un compte visiteur contienne, et la seule dont le
     * support ait a parler avec lui.
     */
    public static function priseIdentiteVisiteur(): Action
    {
        return Action::make('prise_identite')
            ->label(__('Se connecter en tant que'))
            ->tooltip(fn (Visitor $v) => __('Ouvrir le compte de :email', ['email' => $v->email]))
            ->icon('heroicon-o-arrow-right-on-rectangle')
            ->color('gray')
            ->iconButton()
            ->action(fn (Visitor $v) => redirect()->route('admin.prise-identite-visiteur.relais', ['visiteur' => $v]));
    }
}
