<?php

namespace App\Services\Espace;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Filtre le HTML saisi dans l'espace creatif avant enregistrement.
 *
 * Les gabarits des books affichent le corps des pages sans echappement,
 * comme le legacy (qui passait par htmLawed). Ce qui entre ici ressort donc
 * tel quel sur le book : scripts, gestionnaires d'evenements et liens
 * javascript: sont retires.
 */
class NettoyeurHtml
{
    private HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $this->sanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowSafeElements()
                ->allowElement('a', ['href', 'title', 'target'])
                ->allowElement('img', ['src', 'alt', 'title', 'width', 'height'])
                ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
                ->allowMediaSchemes(['http', 'https'])
                ->allowRelativeLinks()
                ->allowRelativeMedias()
                ->forceAttribute('a', 'rel', 'noopener')
                ->withMaxInputLength(200_000)
        );
    }

    public function nettoyer(?string $html): string
    {
        return $this->sanitizer->sanitize((string) $html);
    }
}
