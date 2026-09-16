<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Mots-cles d'un book : normalisation a l'entree, decoupage a la recherche.
 *
 * La colonne d'origine (`inc_user_pref.us_pf_css`, renommee `keywords`) a
 * ete remplie par quinze ans de formulaires successifs. Un echantillon de
 * 4 000 comptes donne, dans le desordre :
 *
 *   illustration jeunesse, aquarelle, presse        liste simple
 *   [&#34; architecture&#34;,&#34; interieur&#34;]  tableau JSON echappe
 *   #fashion, #chanel, #nyc                         hashtags
 *   &lt;meta name=&quot;keywords&quot; content=&quot;flyer,3d&quot;/&gt;
 *   webdesigner, graphiste, 网页设计师，平面设计师   separateur ideographique
 *
 * Les rendre comparables est la condition pour que la recherche trouve la
 * meme chose quelle que soit l'annee de saisie.
 */
final class MotsCles
{
    /** Au-dela, il ne s'agit plus de mots-cles mais d'un texte de presentation. */
    private const MAX_MOTS = 40;

    private const MAX_LONGUEUR = 60;

    /** Virgule, point-virgule, barre, retour a la ligne et leurs variantes CJK. */
    private const SEPARATEURS = '/[,;|\r\n\x{FF0C}\x{3001}\x{FF1B}]+/u';

    /**
     * Forme stockee : mots-cles separes par des virgules, sans balisage.
     */
    public static function normaliser(?string $brut): ?string
    {
        $mots = self::decouper($brut);

        return $mots === [] ? null : implode(',', $mots);
    }

    /**
     * @return list<string>
     */
    public static function decouper(?string $brut): array
    {
        if ($brut === null || trim($brut) === '') {
            return [];
        }

        $texte = self::deshabiller($brut);

        $mots = [];
        $vus = [];

        foreach (preg_split(self::SEPARATEURS, $texte) ?: [] as $mot) {
            $mot = trim(preg_replace('/\s+/u', ' ', $mot) ?? '');

            // Un mot-cle d'une seule lettre ne discrimine rien, et la
            // recherche du legacy ignorait deja les requetes de moins de
            // trois caracteres.
            if (mb_strlen($mot) < 2) {
                continue;
            }

            $mot = mb_substr($mot, 0, self::MAX_LONGUEUR);

            // Dedoublonnage insensible a la casse et aux accents :
            // « Illustration » et « illustration » sont le meme mot-cle.
            $cle = self::comparable($mot);

            if ($cle === '' || isset($vus[$cle])) {
                continue;
            }

            $vus[$cle] = true;
            $mots[] = $mot;

            if (count($mots) >= self::MAX_MOTS) {
                break;
            }
        }

        return $mots;
    }

    /** Forme servant aux comparaisons : minuscules, sans accent ni ponctuation. */
    public static function comparable(string $mot): string
    {
        $ascii = trim(preg_replace('/[^a-z0-9 ]+/', ' ', Str::lower(Str::ascii($mot))) ?? '');

        // Str::ascii ne translittere pas les ideogrammes et rend une chaine
        // vide : quelques books sont references en chinois ou en japonais,
        // et leurs mots-cles doivent rester comparables entre eux.
        return $ascii !== '' ? $ascii : Str::lower($mot);
    }

    /**
     * Retire le balisage accumule au fil des formulaires : entites HTML
     * (parfois encodees deux fois), balise <meta keywords> collee telle
     * quelle, crochets du tableau JSON, guillemets et hashtags.
     */
    private static function deshabiller(string $texte): string
    {
        // Deux passes : certaines valeurs portent &amp;quot; — une entite
        // dont le decodage produit une autre entite.
        for ($i = 0; $i < 2 && str_contains($texte, '&'); $i++) {
            $texte = html_entity_decode($texte, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // Des comptes ont colle la balise entiere dans le champ ; seul son
        // attribut content porte les mots-cles.
        if (preg_match('/<meta[^>]*content\s*=\s*"([^"]*)"/i', $texte, $trouve)) {
            $texte = $trouve[1];
        }

        $texte = strip_tags($texte);

        // L'apostrophe est conservee : elle appartient aux mots-cles
        // (« vue d'ensemble »), contrairement aux guillemets du balisage.
        return str_replace(['[', ']', '"', '#'], ' ', $texte);
    }
}
