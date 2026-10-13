<?php

use App\Models\CmsPage;
use Illuminate\Database\Migrations\Migration;

/*
 | Guides « Comment créer son book / son portfolio » (référencement) : une page
 | pilier et trois déclinaisons, dont le texte vit dans database/content/doc.
 | Les pages existantes de la documentation sont rattachées au pilier par un
 | lien, et le sommaire reçoit enfin un ordre (toutes les positions valaient 0,
 | il se triait par titre).
 */
return new class extends Migration
{
    private const PAGES = [
        'creer-son-book' => [
            'title' => 'Comment créer son book',
            'seo_title' => 'Comment créer son book (portfolio) en ligne gratuitement',
            'excerpt' => 'Créer son book pas à pas : sélectionner ses travaux, les organiser, les légender, choisir un modèle et partager son portfolio en ligne, gratuitement.',
        ],
        'book-etudiant' => [
            'title' => 'Créer son book étudiant',
            'seo_title' => 'Créer son book étudiant : école d’art, Parcoursup, stage',
            'excerpt' => 'Concours d’école d’art, Parcoursup, stage ou alternance : ce que le jury attend d’un book étudiant, et comment le construire gratuitement en ligne.',
        ],
        'book-freelance' => [
            'title' => 'Créer son portfolio de freelance',
            'seo_title' => 'Créer son portfolio de freelance créatif',
            'excerpt' => 'Illustrateur, graphiste, photographe indépendant : comment créer un portfolio de freelance qui fait venir les bons clients.',
        ],
        'book-papier-ou-en-ligne' => [
            'title' => 'Book papier ou portfolio en ligne ?',
            'seo_title' => 'Book papier ou portfolio en ligne : que choisir ?',
            'excerpt' => 'Book papier ou portfolio en ligne : à quoi sert chaque format, et comment les construire à partir de la même sélection de travaux.',
        ],
    ];

    /** Ordre du sommaire de la documentation. */
    private const ORDRE = [
        'creer-son-book', 'les-modeles-de-portfolio', 'book-etudiant', 'book-freelance',
        'book-papier-ou-en-ligne', 'questions-frequentes-2', 'les-formules-ultra-book',
        'compte-visiteur', 'le-memo-book', 'les-newsletters-des-selections', 'droit-dauteur',
        'donnees-personnelles', 'conditions-dutilisations', 'mentions-legales',
    ];

    private const LIEN_DOC = "\n<p>Pour préparer votre sélection et la présenter au mieux, suivez notre guide <a href=\"/doc/creer-son-book\">Comment créer son book, étape par étape</a>.</p>";

    private const LIEN_MODELES = "<p>Le modèle n’est qu’une étape : la méthode complète est décrite dans <a href=\"/doc/creer-son-book\">Comment créer son book</a>.</p>\n";

    public function up(): void
    {
        foreach (self::PAGES as $slug => $page) {
            CmsPage::updateOrCreate(['slug' => $slug, 'locale' => 'fr'], $page + [
                'parent_slug' => 'doc',
                'body' => file_get_contents(database_path("content/doc/{$slug}.html")),
                'published_at' => now(),
            ]);
        }

        foreach (self::ORDRE as $rang => $slug) {
            CmsPage::where('slug', $slug)->where('locale', 'fr')->update(['position' => $rang + 1]);
        }

        // Racine : le <title> porte la requête, le lien mène au pilier
        // juste après les quatre étapes de « Comment ça marche ».
        $racine = CmsPage::where('slug', 'doc')->where('locale', 'fr')->first();
        if ($racine && ! str_contains($racine->body, '/doc/creer-son-book')) {
            $racine->update([
                'seo_title' => 'Créer son book et son portfolio en ligne gratuitement',
                'body' => preg_replace('~</ol>~', '</ol>'.self::LIEN_DOC, $racine->body, 1),
            ]);
        }

        $modeles = CmsPage::where('slug', 'les-modeles-de-portfolio')->where('locale', 'fr')->first();
        if ($modeles && ! str_contains($modeles->body, '/doc/creer-son-book')) {
            $modeles->update(['body' => self::LIEN_MODELES.$modeles->body]);
        }
    }

    public function down(): void
    {
        CmsPage::whereIn('slug', array_keys(self::PAGES))->where('locale', 'fr')->delete();
        CmsPage::whereIn('slug', self::ORDRE)->where('locale', 'fr')->update(['position' => 0]);

        if ($racine = CmsPage::where('slug', 'doc')->where('locale', 'fr')->first()) {
            $racine->update(['seo_title' => null, 'body' => str_replace(self::LIEN_DOC, '', $racine->body)]);
        }
        if ($modeles = CmsPage::where('slug', 'les-modeles-de-portfolio')->where('locale', 'fr')->first()) {
            $modeles->update(['body' => str_replace(self::LIEN_MODELES, '', $modeles->body)]);
        }
    }
};
