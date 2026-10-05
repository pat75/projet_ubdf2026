<?php

namespace App\Services\Admin;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * Series annuelles du tableau de bord : une valeur par semaine, cumulee
 * depuis le 1er janvier, pour poser deux annees sur le meme graphique.
 *
 * Cumul plutot que valeurs brutes : deux annees comparees semaine a
 * semaine donnent deux courbes en dents de scie qui se croisent sans rien
 * dire ; cumulees, l'ecart entre les deux se lit d'un coup d'oeil.
 *
 * Les semaines sont des tranches de sept jours depuis le 1er janvier, et
 * non les semaines ISO : la semaine 10 couvre ainsi les memes dates d'une
 * annee sur l'autre, ce que les semaines ISO ne garantissent pas.
 *
 * Le regroupement se fait en PHP et non en base : `DAYOFYEAR` n'existe pas
 * en SQLite, ou tournent les tests, et le volume d'une annee (quelques
 * milliers de lignes) ne justifie pas deux dialectes SQL.
 *
 * Une source de donnees est un triplet `[requete, colonne de date, colonne
 * a sommer]` ; la colonne a sommer vaut `null` pour compter les lignes.
 * Plusieurs sources s'additionnent dans une meme courbe : c'est ainsi que
 * les desinscriptions reunissent creatifs et visiteurs.
 *
 * @phpstan-type Source array{0: Builder, 1: string, 2: string|null}
 */
class StatistiquesAnnuelles
{
    public const SEMAINES = 53;

    private const CACHE = 600;

    /**
     * Cumul hebdomadaire d'une annee, 53 valeurs.
     *
     * Pour l'annee en cours, les semaines a venir valent `null` : la courbe
     * s'arrete au jour present au lieu de filer a plat jusqu'en decembre.
     *
     * @param  array<int, array{0: Builder, 1: string, 2: string|null}>  $sources
     * @param  string|null  $cle  identifiant de cache ; sans elle, rien n'est garde
     * @return array<int, float|null>
     */
    public function cumul(array $sources, int $annee, ?string $cle = null): array
    {
        $calcul = fn (): array => $this->calculer($sources, $annee);

        return $cle === null
            ? $calcul()
            : Cache::remember("stats.$cle.$annee", self::CACHE, $calcul);
    }

    /**
     * Les trois courbes d'un graphique (annee en cours et deux precedentes), plus l'ecart entre les deux annees
     * arretees a la meme date.
     *
     * @param  array<int, array{0: Builder, 1: string, 2: string|null}>  $sources
     * @return array{serie: array<int, float|null>, serie_precedente: array<int, float|null>, serie_anterieure: array<int, float|null>, total: float, total_precedent: float, ecart: float|null, annee: int, annee_precedente: int, annee_anterieure: int}
     */
    public function comparaison(array $sources, ?string $cle = null): array
    {
        $annee = (int) CarbonImmutable::now()->year;
        $semaine = $this->semaine(CarbonImmutable::now());

        $serie = $this->cumul($sources, $annee, $cle);
        $precedente = $this->cumul($this->cloner($sources), $annee - 1, $cle);
        // Troisieme courbe, en repere : une seule annee de recul ne dit pas
        // si l'ecart est une tendance ou un accident.
        $anterieure = $this->cumul($this->cloner($sources), $annee - 2, $cle);

        $total = (float) ($serie[$semaine - 1] ?? 0.0);
        $totalPrecedent = (float) ($precedente[$semaine - 1] ?? 0.0);

        return [
            'serie' => $serie,
            'serie_precedente' => $precedente,
            'serie_anterieure' => $anterieure,
            'total' => $total,
            'total_precedent' => $totalPrecedent,
            // Sans point de comparaison, pas de pourcentage : « +100 % »
            // par rapport a zero ne veut rien dire.
            'ecart' => $totalPrecedent > 0
                ? round(($total - $totalPrecedent) / $totalPrecedent * 100, 1)
                : null,
            'annee' => $annee,
            'annee_precedente' => $annee - 1,
            'annee_anterieure' => $annee - 2,
        ];
    }

    /** Libelles des 53 semaines : le 1er jour de chaque tranche, « 6 janv. ». */
    public function libelles(int $annee): array
    {
        $janvier = CarbonImmutable::create($annee, 1, 1);

        return array_map(
            fn (int $i) => $janvier->addDays($i * 7)->translatedFormat('j M'),
            range(0, self::SEMAINES - 1),
        );
    }

    /** Numero de semaine (1 a 53) d'une date, tranches de sept jours. */
    private function semaine(CarbonImmutable $date): int
    {
        return min(self::SEMAINES, intdiv($date->dayOfYear - 1, 7) + 1);
    }

    /**
     * @param  array<int, array{0: Builder, 1: string, 2: string|null}>  $sources
     * @return array<int, float|null>
     */
    private function calculer(array $sources, int $annee): array
    {
        $parSemaine = array_fill(0, self::SEMAINES, 0.0);

        foreach ($sources as [$requete, $colonneDate, $somme]) {
            $lignes = (clone $requete)
                ->whereBetween($colonneDate, [
                    CarbonImmutable::create($annee, 1, 1)->startOfDay(),
                    CarbonImmutable::create($annee, 12, 31)->endOfDay(),
                ])
                ->get([$colonneDate, ...($somme ? [$somme] : [])]);

            foreach ($lignes as $ligne) {
                $date = CarbonImmutable::parse($ligne->{$colonneDate});
                $semaine = $this->semaine($date);
                $parSemaine[$semaine - 1] += $somme ? (float) $ligne->{$somme} : 1.0;
            }
        }

        return $this->cumuler($parSemaine, $annee);
    }

    /**
     * @param  array<int, float>  $parSemaine
     * @return array<int, float|null>
     */
    private function cumuler(array $parSemaine, int $annee): array
    {
        $arret = $annee === (int) CarbonImmutable::now()->year
            ? $this->semaine(CarbonImmutable::now())
            : self::SEMAINES;

        $cumul = 0.0;

        return array_map(function (int $i) use ($parSemaine, $arret, &$cumul) {
            if ($i >= $arret) {
                return null;
            }

            $cumul += $parSemaine[$i];

            return round($cumul, 2);
        }, range(0, self::SEMAINES - 1));
    }

    /**
     * Les requetes recues servent deux fois, une annee chacune : sans copie,
     * les clauses de date de la premiere passe s'ajouteraient a la seconde.
     *
     * @param  array<int, array{0: Builder, 1: string, 2: string|null}>  $sources
     * @return array<int, array{0: Builder, 1: string, 2: string|null}>
     */
    private function cloner(array $sources): array
    {
        return array_map(
            fn (array $source) => [clone $source[0], $source[1], $source[2]],
            $sources,
        );
    }
}
