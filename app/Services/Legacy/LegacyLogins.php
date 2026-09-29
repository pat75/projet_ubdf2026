<?php

namespace App\Services\Legacy;

use Illuminate\Support\Facades\DB;

/**
 * Conversion des logins du legacy en noms de sous-domaine valides.
 *
 * Le legacy acceptait « _ » et « . » dans un login (a_menguy), interdits
 * dans un nom d'hote : le book ne pourrait pas etre servi en
 * <login>.<domaine>. Ces caracteres deviennent « - » (a-menguy) ; si ce
 * login est deja pris, « -- » (a--menguy). Si les deux le sont, le compte
 * est ecarte et signale.
 *
 * La table de correspondance est calculee une fois sur tous les comptes
 * vivants, par us_id croissant : elle est donc stable d'une execution a
 * l'autre, et identique pour la reprise en base, les dossiers et les URL
 * d'images des pages.
 */
final class LegacyLogins
{
    /** Login source accepte : les caracteres a convertir compris. */
    public const SOURCE = '^[a-z0-9][a-z0-9_.-]{1,48}$';

    /** Login cible : utilisable comme etiquette de nom d'hote. */
    private const CIBLE = '/^[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/';

    /** @var array<string, string> ancien login => nouveau */
    private array $table = [];

    /** @var list<string> logins sans conversion possible */
    private array $ecartes = [];

    /** @param  iterable<string>  $logins  logins vivants, par us_id croissant */
    public function __construct(iterable $logins)
    {
        $logins = array_map(fn ($l) => mb_strtolower(trim((string) $l)), [...$logins]);
        $pris = [];

        foreach ($logins as $login) {
            if (preg_match(self::CIBLE, $login)) {
                $this->table[$login] = $login;
                $pris[$login] = true;
            }
        }

        foreach ($logins as $login) {
            if (isset($this->table[$login])) {
                continue;
            }

            $base = trim($login, '_.-');
            $candidat = null;

            foreach (['-', '--'] as $separateur) {
                $essai = preg_replace('/[_.]/', $separateur, $base);

                if (preg_match(self::CIBLE, $essai) && ! isset($pris[$essai])) {
                    $candidat = $essai;
                    break;
                }
            }

            if ($candidat === null) {
                $this->ecartes[] = $login;

                continue;
            }

            $this->table[$login] = $candidat;
            $pris[$candidat] = true;
        }
    }

    /** Tous les comptes vivants de ub2020. */
    public static function depuisLegacy(): self
    {
        return new self(DB::connection('legacy')->table('inc_user')
            ->where('us_delete', 'false')
            ->whereRaw("LOWER(us_login) REGEXP '".self::SOURCE."'")
            ->orderBy('us_id')
            ->pluck('us_login'));
    }

    /** Nouveau login, ou null si le compte est ecarte. */
    public function nouveau(?string $ancien): ?string
    {
        return $this->table[mb_strtolower(trim((string) $ancien))] ?? null;
    }

    /** @return array<string, string> ancien => nouveau, pour les seuls renommes */
    public function renommes(): array
    {
        return array_filter($this->table, fn ($nouveau, $ancien) => $nouveau !== $ancien, ARRAY_FILTER_USE_BOTH);
    }

    /** @return list<string> */
    public function ecartes(): array
    {
        return $this->ecartes;
    }

    /**
     * Remplace l'ancien login dans les chemins /users_2/<l>/<l>/<login>/ du
     * HTML des pages, pour que urls_medias_book() retrouve le book.
     */
    public function reecrireChemins(?string $html): ?string
    {
        if ($html === null || ! str_contains($html, '/users_2/')) {
            return $html;
        }

        return preg_replace_callback('#/users_2/((?:[^/"]/){2,3})([^/"]+)/#', function ($m) {
            $nouveau = $this->nouveau($m[2]);

            return $nouveau === null ? $m[0] : '/users_2/'.$m[1].$nouveau.'/';
        }, $html);
    }
}
