<?php

namespace App\Services\Legacy;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Choix de l'echantillon de comptes repris en developpement.
 *
 * Les 6 585 books du legacy pesent 30 Go : on n'en reprend qu'une centaine,
 * choisie pour couvrir tous les cas du portail plutot que les plus recents.
 */
final class LegacySampler
{
    /** @var array<string, true>|null logins dont le dossier existe sur le disque */
    private ?array $onDisk = null;

    public function __construct(
        private readonly int $limit = 100,
        private readonly ?LegacyFiles $files = null,
        private readonly bool $tous = false,
        private readonly int $depuis = 0,
    ) {}

    /**
     * @return Collection<int, object> lignes inc_user
     */
    public function pick(): Collection
    {
        if ($this->tous) {
            return $this->tousLesComptes();
        }

        $selected = collect();

        // 1. Au moins un compte par categorie metier, book rempli et publie.
        foreach (config('categories.list') as $category) {
            $variants = collect(config('categories.legacy_map'))
                ->filter(fn (string $slug) => $slug === $category['slug'])
                ->keys();

            $row = $this->baseQuery()
                ->whereIn(DB::raw('LOWER(us_type)'), $variants)
                ->limit(50)->get()
                ->first(fn ($candidate) => $this->hasFiles($candidate));

            if ($row) {
                $selected->put($row->us_id, $row);
            }
        }

        // 2. Des comptes abonnes, pour couvrir formules et facturation.
        $this->baseQuery()->where('us_formule', '>', 0)->limit(90)
            ->get()->filter(fn ($row) => $this->hasFiles($row))->take(15)
            ->each(fn ($row) => $selected->put($row->us_id, $row));

        // 3. Des comptes marque Dustfolio.
        $this->baseQuery()->where('us_view', 'df')->limit(60)
            ->get()->filter(fn ($row) => $this->hasFiles($row))->take(10)
            ->each(fn ($row) => $selected->put($row->us_id, $row));

        // 4. Des comptes en ultra-selection et en annuaire.
        $this->baseQuery()->where('us_ultraselection', 'true')->limit(60)
            ->get()->filter(fn ($row) => $this->hasFiles($row))->take(10)
            ->each(fn ($row) => $selected->put($row->us_id, $row));

        // 5. Les books les mieux remplis, pour eprouver le rendu.
        $this->baseQuery()->limit($this->limit * 8)
            ->get()->filter(fn ($row) => $this->hasFiles($row))
            ->each(function ($row) use ($selected) {
                if ($selected->count() < $this->limit) {
                    $selected->put($row->us_id, $row);
                }
            });

        return $selected->take($this->limit)->values();
    }

    /**
     * Mise en production : un paquet de `limit` comptes vivants, a partir
     * de us_id > `depuis`, sans seuil de visuels ni verification du disque.
     * Les logins a « _ » ou « . » sont pris : LegacyLogins les convertit.
     *
     * @return Collection<int, object>
     */
    private function tousLesComptes(): Collection
    {
        return DB::connection('legacy')->table('inc_user')
            ->where('us_delete', 'false')
            ->whereRaw("LOWER(us_login) REGEXP '".LegacyLogins::SOURCE."'")
            ->where('us_id', '>', $this->depuis)
            ->orderBy('us_id')
            ->limit($this->limit)
            ->get();
    }

    /**
     * Comptes vivants, dont le book contient des visuels publies, les plus
     * fournis d'abord.
     */
    private function baseQuery()
    {
        return DB::connection('legacy')->table('inc_user')
            ->select('inc_user.*')
            ->selectSub(
                DB::connection('legacy')->table('ub2_gal_img')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('img_id_us', 'inc_user.us_id')
                    ->where('img_publier', 'publie'),
                'media_count'
            )
            ->where('us_delete', 'false')
            ->where('us_login', '<>', '')
            ->whereRaw("us_login REGEXP '^[a-z0-9][a-z0-9-]{1,48}$'")
            ->havingRaw('media_count >= 5')
            ->orderByDesc('media_count');
    }

    /**
     * Un compte n'est retenu que si son dossier existe sur le disque : sans
     * ses visuels, le book ne permet pas d'eprouver le rendu. Le poste de
     * developpement ne detient que 5 276 des books de production.
     */
    private function hasFiles(object $row): bool
    {
        if ($this->files === null) {
            return true;
        }

        if ($this->onDisk === null) {
            $this->onDisk = [];
            $root = config('ubdf.legacy_books_path');

            foreach (glob($root.'/*/*/*/img_', GLOB_ONLYDIR) ?: [] as $path) {
                $this->onDisk[basename(dirname($path))] = true;
            }
        }

        return isset($this->onDisk[$row->us_login]);
    }
}
