<?php

namespace App\Services\Espace;

/**
 * Traduit les blocs de l'editeur maison (Editor.js, resources/js/espace-blocs.js,
 * App\Livewire\Espace\Pages) en HTML equivalent, ecrit dans book_articles.body
 * a cote de body_blocks : l'affichage public d'une page n'a ainsi jamais a
 * savoir avec quel editeur elle a ete ecrite.
 *
 * Les trois types geres correspondent aux outils installes cote JS
 * (@editorjs/header, @editorjs/paragraph, @editorjs/image) :
 *   - header    : { text, level }
 *   - paragraph : { text }
 *   - image     : { file: { url }, caption }
 */
class RenduBlocsPage
{
    /** @param  list<array{type: string, data: array<string, mixed>}>  $blocs */
    public function versHtml(array $blocs): string
    {
        return collect($blocs)
            ->map(fn (array $bloc) => $this->rendreBloc($bloc))
            ->filter()
            ->implode("\n");
    }

    /** @param  array{type: string, data: array<string, mixed>}  $bloc */
    private function rendreBloc(array $bloc): string
    {
        $donnees = $bloc['data'] ?? [];

        return match ($bloc['type'] ?? null) {
            'header' => $this->titre($donnees),
            'paragraph' => $this->paragraphe($donnees),
            'image' => $this->image($donnees),
            default => '',
        };
    }

    private function titre(array $donnees): string
    {
        $texte = trim((string) ($donnees['text'] ?? ''));

        if ($texte === '') {
            return '';
        }

        // Le premier niveau de titre du corps reste sous le h1 de la page :
        // 2 a 4 seulement, comme les niveaux proposes par l'outil cote JS.
        $niveau = min(4, max(2, (int) ($donnees['level'] ?? 2)));

        return "<h{$niveau}>{$texte}</h{$niveau}>";
    }

    private function paragraphe(array $donnees): string
    {
        $texte = trim((string) ($donnees['text'] ?? ''));

        return $texte === '' ? '' : "<p>{$texte}</p>";
    }

    private function image(array $donnees): string
    {
        $url = $donnees['file']['url'] ?? null;

        if (! $url) {
            return '';
        }

        $legende = trim((string) ($donnees['caption'] ?? ''));
        $figcaption = $legende !== '' ? "<figcaption>{$legende}</figcaption>" : '';

        return '<figure><img src="'.e($url).'" alt="'.e($legende).'">'.$figcaption.'</figure>';
    }
}
