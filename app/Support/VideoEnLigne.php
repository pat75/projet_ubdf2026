<?php

namespace App\Support;

/**
 * Une video YouTube ou Vimeo, reconnue a partir du lien colle par le
 * createur : page de la video, lien court, Shorts ou lecteur integre.
 */
final class VideoEnLigne
{
    private function __construct(
        public readonly string $plateforme,
        public readonly string $identifiant,
    ) {}

    public static function depuis(?string $lien): ?self
    {
        $lien = trim((string) $lien);

        if (! preg_match('#^https?://#i', $lien)) {
            $lien = 'https://'.$lien;
        }

        $hote = strtolower((string) parse_url($lien, PHP_URL_HOST));
        $hote = preg_replace('/^(www\.|m\.)/', '', $hote);
        $chemin = (string) parse_url($lien, PHP_URL_PATH);
        parse_str((string) parse_url($lien, PHP_URL_QUERY), $requete);

        $youtube = match (true) {
            $hote === 'youtu.be' => trim($chemin, '/'),
            in_array($hote, ['youtube.com', 'youtube-nocookie.com', 'music.youtube.com'], true) => preg_match('#^/(?:embed|shorts|live|v)/([^/]+)#', $chemin, $m)
                ? $m[1]
                : (is_string($requete['v'] ?? null) ? $requete['v'] : ''),
            default => null,
        };

        if ($youtube !== null) {
            return preg_match('/^[A-Za-z0-9_-]{11}$/', $youtube) ? new self('youtube', $youtube) : null;
        }

        if (in_array($hote, ['vimeo.com', 'player.vimeo.com'], true)
            && preg_match('#^/(?:video/|channels/[^/]+/|groups/[^/]+/videos/)?(\d{5,12})(?:/|$)#', $chemin, $m)) {
            return new self('vimeo', $m[1]);
        }

        return null;
    }

    /** Lien canonique, celui qu'on enregistre. */
    public function lien(): string
    {
        return $this->plateforme === 'youtube'
            ? 'https://www.youtube.com/watch?v='.$this->identifiant
            : 'https://vimeo.com/'.$this->identifiant;
    }

    /** Lecteur integre, lance a l'ouverture. YouTube sans cookie de pistage. */
    public function lecteur(): string
    {
        return $this->plateforme === 'youtube'
            ? 'https://www.youtube-nocookie.com/embed/'.$this->identifiant.'?autoplay=1&rel=0'
            : 'https://player.vimeo.com/video/'.$this->identifiant.'?autoplay=1';
    }

    public function nomPlateforme(): string
    {
        return $this->plateforme === 'youtube' ? 'YouTube' : 'Vimeo';
    }
}
