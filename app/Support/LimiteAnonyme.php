<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Listes de portfolios bornees pour un anonyme : une ligne de cartes, puis
 * une invitation a se connecter ou a ouvrir un compte visiteur gratuit
 * (composant <x-portail.limite-anonyme>).
 *
 * Appliquee cote serveur a chaque porte de la liste (premier ecran,
 * defilement infini, contrat JSON legacy) : masquer les cartes dans le
 * navigateur laisserait la suite lisible par simple appel d'URL.
 *
 * Utilisee par la recherche ; une page metier s'y branche de la meme
 * facon (voir RechercheController::donnees et ::cartes).
 */
final class LimiteAnonyme
{
    /** Une ligne de la grille « five cards » (recherche). */
    public const BOOKS = 5;

    /** Deux lignes sur les pages metier. */
    public const BOOKS_METIER = 10;

    /** Une ligne de la grille « six cards » des resultats images. */
    public const IMAGES = 6;

    /** Cle de session : page ou ramener le visiteur apres creation de son compte. */
    public const CLE_RETOUR = 'visiteur.retour';

    public static function active(): bool
    {
        return ! auth('web')->check() && ! auth('visitor')->check();
    }

    /** La liste est-elle tronquee pour cet utilisateur ? */
    public static function bornee(int $total, int $visibles = self::BOOKS): bool
    {
        return self::active() && $total > $visibles;
    }

    /**
     * Premier ecran : les $visibles premiers portfolios si la liste est bornee.
     * Memorise la page pour y revenir apres l'inscription.
     *
     * @template T
     *
     * @param  Collection<int, T>  $books
     * @return Collection<int, T>
     */
    public static function premierEcran(Collection $books, int $total, int $visibles = self::BOOKS): Collection
    {
        if (! self::bornee($total, $visibles)) {
            return $books;
        }

        // ponytail: derniere page bornee vue, meme si le compte est ouvert
        // plus tard depuis un coeur ailleurs ; drapeau par fenetre si genant.
        session([self::CLE_RETOUR => request()->getRequestUri()]);

        return $books->take($visibles);
    }

    /**
     * Pages suivantes (defilement infini, ?page=N) : rien pour un anonyme.
     *
     * @template T
     *
     * @param  Collection<int, T>  $books
     * @return Collection<int, T>
     */
    public static function pageSuivante(Collection $books): Collection
    {
        return self::active() ? $books->take(0) : $books;
    }

    /** Page a rouvrir apres inscription d'un visiteur, sinon $defaut. */
    public static function retour(string $defaut): string
    {
        return session()->pull(self::CLE_RETOUR) ?? $defaut;
    }
}
