<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Bandeau de revue, pose en haut du book ouvert depuis le back-office.
 *
 * Injecte dans le HTML rendu plutot qu'ajoute aux gabarits : les books
 * ont dix modeles, dont la plupart sont des gabarits legacy qu'on ne
 * retouche pas. BookController::reponse() est le seul passage commun a
 * tous — c'est la que LecteurVideos intervient deja, pour la meme raison.
 *
 * Le bandeau ne s'affiche que sur la page ouverte par la revue et apres
 * un changement de selection : il suit le jeton, pas une session. Naviguer
 * librement dans le book le fait disparaitre, ce qui convient au geste
 * vise — ouvrir, juger, selectionner, refermer l'onglet.
 */
class BandeauRevue
{
    public function __construct(private readonly RevueBooks $revue) {}

    public function injecter(string $html, User $book, Request $requete): string
    {
        $jeton = (string) $requete->query(RevueBooks::PARAMETRE, '');

        if ($this->revue->administrateur($jeton, $book) === null) {
            return $html;
        }

        $bandeau = view('book.commun.bandeau-revue', [
            'book' => $book,
            'jeton' => $jeton,
        ])->render();

        // Apres l'ouverture de <body> : le bandeau doit rester en tete de
        // page quel que soit le theme, meme ceux qui positionnent tout en
        // absolu (le decalage du contenu est fait en CSS dans la vue).
        if (preg_match('~<body[^>]*>~i', $html, $trouve, PREG_OFFSET_CAPTURE)) {
            return substr_replace($html, $bandeau, $trouve[0][1] + strlen($trouve[0][0]), 0);
        }

        return $bandeau.$html;
    }
}
