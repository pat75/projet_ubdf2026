<?php

namespace App\Services\Espace;

use App\Models\Conversation;
use App\Models\DataExport;
use App\Models\Gallery;
use App\Models\Media;
use App\Models\User;
use App\Support\DossierBook;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * Archive « Mes donnees » d'un createur, dans l'esprit du telechargement
 * de ses informations chez Facebook : tout ce que le compte contient, lisible
 * par une machine (JSON) et par un humain (index.html).
 *
 *   index.html              sommaire lisible, ouvre les images
 *   compte.json             fiche du compte, metier, reglages, facturation
 *   portfolios.json         galeries et visuels (fichier de chaque image)
 *   pages.json              rubriques, pages et actualites
 *   messages.json           demandes recues et echanges
 *   factures.json           factures
 *   images/<galerie>/...    visuels en haute definition (fichiers d'origine)
 *   profil/...              visuel de profil, photo de presentation, fond
 *   pages/...               images inserees dans les pages
 *
 * Ne sortent jamais : mot de passe, jetons, note interne de l'equipe,
 * mots de passe des galeries protegees, donnees brutes de la reprise.
 */
final class ExportCompte
{
    private const JSON = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;

    /** Champs retires de toute sortie, quel que soit le modele. */
    private const EXCLUS = ['password', 'remember_token', 'google_id', 'admin_note', 'legacy_payload',
        'legacy_token', 'owner_token', 'sender_token', 'gateway_payload'];

    /** Chemin de l'archive, relatif au disque DataExport::DISQUE. */
    public function construire(User $creatif): string
    {
        // Un dossier par createur (son id : le login peut changer), nom non devinable.
        $dossier = DataExport::DOSSIER.'/'.$creatif->id;
        $relatif = $dossier.'/'.$creatif->login.'_'.now()->format('Ymd_His').'_'.Str::lower(Str::random(16)).'.zip';
        $disque = Storage::disk(DataExport::DISQUE);
        $disque->makeDirectory($dossier);
        $chemin = $disque->path($relatif);

        $zip = new ZipArchive;
        if ($zip->open($chemin, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
            throw new RuntimeException("Archive impossible : {$chemin}");
        }

        $creatif->loadMissing(['category', 'bookSetting', 'billingProfile']);

        $compte = $this->compte($creatif);
        $portfolios = $this->portfolios($creatif, $zip);
        $profil = $this->profil($creatif, $zip);
        $pages = $this->pages($creatif, $zip);
        $messages = $this->messages($creatif);
        $factures = $creatif->invoices()->orderBy('issued_at')->get()->map($this->nettoyer(...))->all();

        $zip->addFromString('compte.json', json_encode(['exporte_le' => now()->toIso8601String()] + $compte + ['fichiers_profil' => $profil], self::JSON));
        $zip->addFromString('portfolios.json', json_encode($portfolios, self::JSON));
        $zip->addFromString('pages.json', json_encode($pages, self::JSON));
        $zip->addFromString('messages.json', json_encode($messages, self::JSON));
        $zip->addFromString('factures.json', json_encode($factures, self::JSON));
        $zip->addFromString('index.html', view('exports.index', [
            'creatif' => $creatif,
            'profil' => $profil,
            'portfolios' => $portfolios,
            'pages' => $pages,
            'messages' => $messages,
            'factures' => $factures,
        ])->render());

        if (! $zip->close()) {
            @unlink($chemin);
            throw new RuntimeException("Archive incomplete : {$chemin}");
        }

        // ZipArchive ecrit sans passer par le disque : droits du disque reappliques.
        @chmod($chemin, 0600);

        return $relatif;
    }

    /** @return array<string, mixed> */
    private function compte(User $creatif): array
    {
        return [
            'compte' => $this->nettoyer($creatif->withoutRelations()),
            'metier' => $creatif->category?->only(['slug', 'name']),
            'reglages_du_book' => $creatif->bookSetting ? $this->nettoyer($creatif->bookSetting) : null,
            'facturation' => $creatif->billingProfile ? $this->nettoyer($creatif->billingProfile) : null,
        ];
    }

    /**
     * Galeries dans l'ordre du book, visuels dans l'ordre de chaque galerie ;
     * chaque image est copiee dans images/<nn>-<galerie>/.
     *
     * @return list<array<string, mixed>>
     */
    private function portfolios(User $creatif, ZipArchive $zip): array
    {
        $galeries = $creatif->galleries()->orderBy('parent_id')->orderBy('position')->get();
        $resultat = [];

        foreach ($galeries->values() as $i => $galerie) {
            $dossier = sprintf('images/%02d-%s', $i + 1, Str::slug($galerie->name) ?: 'galerie');
            $resultat[] = $this->nettoyer($galerie->makeHidden('password')) + [
                'protegee' => $galerie->estProtegee(),
                'visuels' => $this->visuels($creatif, $this->ordonner($galerie), $dossier, $zip),
            ];
        }

        // Visuels sans galerie : rien ne se perd.
        $orphelins = $creatif->media()->whereNull('gallery_id')->orderBy('id')->get();
        if ($orphelins->isNotEmpty()) {
            $resultat[] = ['name' => 'Sans galerie', 'visuels' => $this->visuels($creatif, $orphelins, 'images/sans-galerie', $zip)];
        }

        return $resultat;
    }

    /** @return \Illuminate\Support\Collection<int, Media> */
    private function ordonner(Gallery $galerie)
    {
        $ordre = array_flip($galerie->media_order ?? []);

        return $galerie->media()->get()
            ->sortBy(fn (Media $m) => [$ordre[(string) ($m->legacy_id ?? $m->id)] ?? PHP_INT_MAX, $m->position, $m->id])
            ->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Media>  $visuels
     * @return list<array<string, mixed>>
     */
    private function visuels(User $creatif, $visuels, string $dossier, ZipArchive $zip): array
    {
        return $visuels->values()->map(function (Media $m, int $i) use ($creatif, $dossier, $zip) {
            $fichier = $m->filename
                ? $this->ajouter($zip, DossierBook::chemin($creatif->login, $m->filename), sprintf('%s/%03d-%s', $dossier, $i + 1, basename($m->filename)))
                : null;

            return $this->nettoyer($m) + ['fichier' => $fichier];
        })->all();
    }

    /** @return array<string, ?string> */
    private function profil(User $creatif, ZipArchive $zip): array
    {
        $reglages = $creatif->bookSetting;
        $fichiers = [];

        foreach (['visuel_de_profil' => 'thumbnail', 'photo_de_presentation' => 'bio_photo', 'image_de_fond' => 'background_image'] as $nom => $champ) {
            $valeur = $reglages?->{$champ};
            $fichiers[$nom] = $valeur
                ? $this->ajouter($zip, DossierBook::chemin($creatif->login, basename($valeur)), 'profil/'.$nom.'-'.basename($valeur))
                : null;
        }

        return $fichiers;
    }

    /** @return array<string, mixed> */
    private function pages(User $creatif, ZipArchive $zip): array
    {
        $images = $creatif->pageImages()->orderBy('id')->get()->map(fn ($image) => $this->nettoyer($image) + [
            'fichier' => $this->ajouter($zip, DossierBook::chemin($creatif->login, DepotImagePage::DOSSIER.'/'.$image->filename), 'pages/'.basename($image->filename)),
        ]);

        return [
            'rubriques' => $creatif->sections()->orderBy('position')->get()->map($this->nettoyer(...))->all(),
            'pages_et_actualites' => $creatif->articles()->orderBy('book_section_id')->orderBy('position')->get()->map($this->nettoyer(...))->all(),
            'images' => $images->all(),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function messages(User $creatif): array
    {
        return $creatif->conversations()->with(['messages' => fn ($q) => $q->orderBy('created_at')])
            ->orderBy('created_at')->get()
            ->map(fn (Conversation $c) => $this->nettoyer($c->withoutRelations()) + [
                'messages' => $c->messages->map(fn ($m) => [
                    'de' => $m->from_owner ? 'moi' : ($c->sender_name ?: $c->sender_email),
                    'date' => $m->created_at?->toIso8601String(),
                    'lu_le' => $m->read_at?->toIso8601String(),
                    'texte' => $m->body,
                ])->all(),
            ])->all();
    }

    /** Copie un fichier dans l'archive ; son chemin dans l'archive, ou null s'il manque. */
    private function ajouter(ZipArchive $zip, string $source, string $cible): ?string
    {
        if (! is_file($source)) {
            return null;
        }

        // Les images sont deja compressees : les stocker sans recompression.
        $zip->addFile($source, $cible);
        $zip->setCompressionName($cible, ZipArchive::CM_STORE);

        return $cible;
    }

    /** @return array<string, mixed> */
    private function nettoyer($modele): array
    {
        return array_diff_key($modele->toArray(), array_flip(self::EXCLUS));
    }
}
