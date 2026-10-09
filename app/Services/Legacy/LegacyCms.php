<?php

namespace App\Services\Legacy;

use App\Models\CmsPage;
use App\Models\CmsPost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Import des pages et actualites de l'ancien WordPress du magazine.
 *
 * Le portail de 2019 ne stockait pas ces contenus : il demarrait WordPress
 * dans son propre processus et l'interrogeait a chaque requete. Ils sont
 * repris ici une fois pour toutes, avec leurs URL.
 */
class LegacyCms
{
    /** Racines des deux arbres de documentation, une par langue. */
    private const RACINES_DOC = [2 => 'fr', 844 => 'en'];

    /** @var array<string, int> */
    private array $comptes = [];

    /** @var list<string> */
    private array $avertissements = [];

    /**
     * @return array{comptes: array<string, int>, avertissements: list<string>}
     */
    public function importer(bool $avecMedias = true): array
    {
        $langues = $this->languesWpml();

        $this->importerPages($langues);
        $this->importerActualites($langues);

        if ($avecMedias) {
            $this->copierMedias();
        }

        return ['comptes' => $this->comptes, 'avertissements' => $this->avertissements];
    }

    /**
     * Langue et groupe de traduction de chaque contenu, d'apres WPML.
     *
     * @return array<int, array{locale: string, trid: int}>
     */
    private function languesWpml(): array
    {
        return DB::connection('legacy_wp')->table('icl_translations')
            ->whereIn('element_type', ['post_page', 'post_post'])
            ->get()
            ->keyBy('element_id')
            ->map(fn ($ligne) => [
                'locale' => substr((string) $ligne->language_code, 0, 5),
                'trid' => (int) $ligne->trid,
            ])
            ->all();
    }

    /**
     * @param  array<int, array{locale: string, trid: int}>  $langues
     */
    private function importerPages(array $langues): void
    {
        $lignes = DB::connection('legacy_wp')->table('posts')
            ->where('post_type', 'page')
            ->where('post_status', 'publish')
            ->orderBy('menu_order')
            ->orderBy('ID')
            ->get();

        $slugParId = $lignes->pluck('post_name', 'ID')->all();
        $compte = 0;

        foreach ($lignes as $ligne) {
            $id = (int) $ligne->ID;

            CmsPage::updateOrCreate(
                ['legacy_id' => $id],
                [
                    'translation_group' => $langues[$id]['trid'] ?? null,
                    'slug' => $ligne->post_name,
                    'locale' => $this->locale($id, $ligne->post_parent, $langues),
                    'parent_slug' => $slugParId[(int) $ligne->post_parent] ?? null,
                    'title' => $this->texte($ligne->post_title),
                    'body' => $this->corps($ligne->post_content, $ligne->post_title),
                    'excerpt' => $this->texte($ligne->post_excerpt) ?: null,
                    'position' => (int) $ligne->menu_order,
                    'published_at' => $this->date($ligne->post_date),
                ],
            );

            $compte++;
        }

        $this->comptes['pages'] = $compte;
    }

    /**
     * @param  array<int, array{locale: string, trid: int}>  $langues
     */
    private function importerActualites(array $langues): void
    {
        $lignes = DB::connection('legacy_wp')->table('posts')
            ->where('post_type', 'post')
            ->where('post_status', 'publish')
            ->orderByDesc('post_date')
            ->get();

        $compte = 0;

        foreach ($lignes as $ligne) {
            $id = (int) $ligne->ID;

            CmsPost::updateOrCreate(
                ['legacy_id' => $id],
                [
                    'slug' => $this->slugWordPress((string) $ligne->post_name) ?: Str::slug($ligne->post_title) ?: 'actu-'.$id,
                    'locale' => $langues[$id]['locale'] ?? 'fr',
                    'title' => $this->texte($ligne->post_title),
                    'body' => $this->corps($ligne->post_content, $ligne->post_title),
                    'excerpt' => $this->texte($ligne->post_excerpt) ?: null,
                    'image' => $this->imageMiseEnAvant($id),
                    'published_at' => $this->date($ligne->post_date),
                ],
            );

            $compte++;
        }

        $this->comptes['actualites'] = $compte;
    }

    /**
     * WordPress stocke encode un slug a caractere non ASCII
     * (« d%e2%80%99illustrateurs ») : tel quel, aucune route ne le sert. Les
     * autres restent intacts, soulignes compris : leurs adresses sont indexees.
     */
    private function slugWordPress(string $slug): string
    {
        return str_contains($slug, '%') ? Str::slug(rawurldecode($slug)) : $slug;
    }

    /**
     * Langue d'une page : celle que declare WPML, sinon celle de l'arbre de
     * documentation auquel elle appartient.
     *
     * @param  array<int, array{locale: string, trid: int}>  $langues
     */
    private function locale(int $id, int|string|null $parent, array $langues): string
    {
        return $langues[$id]['locale']
            ?? self::RACINES_DOC[(int) $parent]
            ?? 'fr';
    }

    /**
     * Resout les trois codes courts du theme.
     *
     * `perso` et `clear` produisent du HTML fixe : ils sont developpes ici,
     * une fois, plutot que de reconduire un moteur de codes courts.
     *
     * `[ub_formule]` n'a en revanche **aucun gestionnaire** dans le site de
     * 2019 : les deux pages de tarifs affichent donc le code court en
     * toutes lettres. Il est retire, et la page signalee — le bloc de
     * tarifs sera rendu en phase 7, avec le paiement.
     */
    private function corps(?string $html, ?string $titre): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $html = $this->texte($html) ?? '';

        $html = preg_replace_callback(
            '/\[perso\s+([^\]]*)\]/i',
            fn (array $trouve) => $this->blocPerso($this->attributs($trouve[1])),
            $html,
        ) ?? $html;

        $html = preg_replace('/\[clear\s*\]/i', '<br clear="all"/>', $html) ?? $html;

        if (preg_match('/\[ub_formule[^\]]*\]/i', $html)) {
            $this->avertissements[] = sprintf(
                'Page « %s » : [ub_formule] retire, le bloc de tarifs reste a produire (phase 7).',
                $this->texte($titre),
            );

            $html = preg_replace('/\[ub_formule[^\]]*\]/i', '', $html) ?? $html;
        }

        return trim($html);
    }

    /**
     * @param  array<string, string>  $attributs
     */
    private function blocPerso(array $attributs): string
    {
        $image = $attributs['img'] ?? '';

        if ($image === '') {
            $image = '/magazine/wp-content/uploads/2010/12/tea24_big_gris.gif';
        }

        return view('front.cms.perso', [
            'nom' => $attributs['name'] ?? '',
            'role' => $attributs['role'] ?? '',
            'image' => $image,
            'style' => $attributs['style'] ?? '',
        ])->render();
    }

    /**
     * @return array<string, string>
     */
    private function attributs(string $brut): array
    {
        preg_match_all('/([a-z_]+)\s*=\s*"([^"]*)"/i', $brut, $trouves, PREG_SET_ORDER);

        $attributs = [];

        foreach ($trouves as $trouve) {
            $attributs[strtolower($trouve[1])] = $trouve[2];
        }

        return $attributs;
    }

    private function imageMiseEnAvant(int $id): ?string
    {
        $idImage = DB::connection('legacy_wp')->table('postmeta')
            ->where('post_id', $id)
            ->where('meta_key', '_thumbnail_id')
            ->value('meta_value');

        if (! $idImage) {
            return null;
        }

        $chemin = DB::connection('legacy_wp')->table('postmeta')
            ->where('post_id', (int) $idImage)
            ->where('meta_key', '_wp_attached_file')
            ->value('meta_value');

        return $chemin ? '/magazine/wp-content/uploads/'.$chemin : null;
    }

    /**
     * Copie les seuls fichiers reellement cites par les contenus importes.
     *
     * Le dossier d'origine pese 80 Mo pour 531 fichiers, essentiellement des
     * declinaisons produites par WordPress et jamais referencees.
     */
    private function copierMedias(): void
    {
        $source = rtrim((string) config('ubdf.legacy_path'), '/').'/magazine/wp-content/uploads/';

        if (! File::isDirectory($source)) {
            $this->avertissements[] = "Medias introuvables : {$source}";
            $this->comptes['medias'] = 0;

            return;
        }

        $contenus = CmsPage::pluck('body')
            ->merge(CmsPost::pluck('body'))
            ->merge(CmsPost::pluck('image'))
            ->implode("\n");

        preg_match_all('#/magazine/wp-content/uploads/([^"\'\s\)]+)#', $contenus, $trouves);

        $copies = 0;

        foreach (array_unique($trouves[1]) as $relatif) {
            // Le chemin vient du contenu : il ne doit pas pouvoir remonter
            // hors du dossier d'origine.
            if (str_contains($relatif, '..')) {
                continue;
            }

            $depuis = $source.$relatif;
            $vers = public_path('magazine/wp-content/uploads/'.$relatif);

            if (! File::isFile($depuis)) {
                $this->avertissements[] = "Media absent du disque : {$relatif}";

                continue;
            }

            File::ensureDirectoryExists(dirname($vers));
            File::copy($depuis, $vers);
            $copies++;
        }

        $this->comptes['medias'] = $copies;
    }

    private function texte(?string $valeur): ?string
    {
        // La base WordPress est propre, contrairement a ub2020 : rien a
        // reparer, seulement a normaliser.
        return $valeur === null ? null : \Normalizer::normalize($valeur, \Normalizer::FORM_C);
    }

    private function date(?string $valeur): ?string
    {
        return $valeur && ! str_starts_with($valeur, '0000') ? $valeur : null;
    }
}
