<?php

namespace App\Services\Book;

use App\Models\Media;
use App\Models\User;

/**
 * Lecture des videos YouTube et Vimeo dans le book, quel que soit le theme.
 *
 * Les gabarits legacy ne connaissent que des images : une video y figure
 * par sa vignette (bouton lecture compris, voir DepotVisuel). Un script
 * ajoute en fin de page reconnait ces vignettes a leur nom de fichier et
 * ouvre le lecteur par-dessus la page au clic, avant la visionneuse du
 * theme.
 */
class LecteurVideos
{
    public function injecter(string $html, User $book): string
    {
        $lecteurs = Media::where('user_id', $book->id)->published()->whereNotNull('video_url')
            ->get(['filename', 'video_url'])
            ->mapWithKeys(fn (Media $m) => [(string) $m->filename => $m->video()?->lecteur()])
            ->filter()
            ->all();

        if ($lecteurs === []) {
            return $html;
        }

        $script = '<script>'.str_replace('__LECTEURS__', json_encode($lecteurs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES), self::SCRIPT).'</script>';
        $fin = strripos($html, '</body>');

        return $fin === false ? $html.$script : substr_replace($html, $script, $fin, 0);
    }

    private const SCRIPT = <<<'JS'
(function () {
    var lecteurs = __LECTEURS__;

    function lecteur(el) {
        var img = el.tagName === 'IMG' ? el : el.querySelector && el.querySelector('img');
        var src = img ? img.getAttribute('src') || '' : (el.getAttribute && el.getAttribute('href')) || '';
        var nom = src.split('?')[0].split('/').pop();
        return lecteurs[nom] || null;
    }

    function ouvrir(url) {
        var fond = document.createElement('div');
        fond.setAttribute('style', 'position:fixed;inset:0;z-index:2147483647;background:rgba(0,0,0,.85);display:flex;align-items:center;justify-content:center;padding:4vw');
        fond.innerHTML = '<div style="position:relative;width:100%;max-width:1100px;aspect-ratio:16/9">'
            + '<iframe src="' + url + '" style="position:absolute;inset:0;width:100%;height:100%;border:0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>'
            + '<button type="button" aria-label="Fermer" style="position:absolute;top:-44px;right:0;background:none;border:0;color:#fff;font-size:34px;line-height:1;cursor:pointer">&times;</button></div>';
        function fermer() { fond.remove(); document.removeEventListener('keydown', touche); }
        function touche(e) { if (e.key === 'Escape') fermer(); }
        fond.addEventListener('click', function (e) { if (e.target === fond || e.target.tagName === 'BUTTON') fermer(); });
        document.addEventListener('keydown', touche);
        document.body.appendChild(fond);
    }

    // Phase de capture : le clic est pris avant la visionneuse du theme.
    document.addEventListener('click', function (e) {
        for (var el = e.target; el && el !== document; el = el.parentNode) {
            var url = (el.tagName === 'IMG' || el.tagName === 'A') && lecteur(el);
            if (url) {
                e.preventDefault();
                e.stopImmediatePropagation();
                ouvrir(url);
                return;
            }
        }
    }, true);
})();
JS;
}
