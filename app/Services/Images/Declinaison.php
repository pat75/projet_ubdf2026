<?php

namespace App\Services\Images;

/**
 * Une declinaison nommee : sa boite, son mode de cadrage.
 *
 * Objet-valeur volontairement ferme : seules les declinaisons declarees
 * dans `config/images.php` existent. C'est ce qui separe ce service de
 * phpThumb, que le legacy laissait recevoir ses dimensions depuis l'URL.
 */
readonly class Declinaison
{
    private function __construct(
        public string $nom,
        public int $largeur,
        public int $hauteur,
        public string $mode,
    ) {}

    public static function nommee(?string $nom): ?self
    {
        $nom ??= config('images.defaut');
        $conf = config('images.declinaisons.'.$nom);

        if (! is_array($conf)) {
            return null;
        }

        return new self($nom, $conf['largeur'], $conf['hauteur'], $conf['mode']);
    }

    /** @return list<string> */
    public static function noms(): array
    {
        return array_keys(config('images.declinaisons', []));
    }

    public function rogne(): bool
    {
        return $this->mode === 'cover';
    }
}
