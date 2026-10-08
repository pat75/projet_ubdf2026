<?php

namespace App\Services\Coach;

use App\Models\CreatifActivity;
use App\Models\User;
use App\Services\IA\Nvidia;
use Illuminate\Support\Collection;
use RuntimeException;

/** Redige le message de coaching avec NVIDIA, a partir de resources/prompts/coach.md. */
class Redacteur
{
    /** Modele texte : seul teste le 2026-10-08 a repondre (9 s) dans un francais propre et au format attendu. */
    public const MODELE = 'nvidia/nemotron-3-super-120b-a12b';

    public function __construct(private Nvidia $nvidia) {}

    /**
     * @param  Collection<int, CreatifActivity>  $operations
     * @param  list<string>  $conseils
     * @return array{objet: string, corps: string, modele: string}
     */
    public function rediger(User $creatif, Collection $operations, array $conseils): array
    {
        $contexte = "Créatif : {$creatif->fullName()}\n"
            ."Book : {$creatif->bookUrl()}\n\n"
            ."Opérations de la dernière session :\n- ".$operations->map->resume()->unique()->implode("\n- ")."\n\n"
            ."Conseils à transmettre (dans cet ordre, n'en ajoute aucun) :\n- ".implode("\n- ", $conseils);

        $reponse = $this->nvidia->chat([
            ['role' => 'system', 'content' => file_get_contents(resource_path('prompts/coach.md'))],
            ['role' => 'user', 'content' => $contexte],
        ], self::MODELE);

        $texte = trim($reponse['data']['choices'][0]['message']['content'] ?? '');

        // Format attendu : « Objet : … » sur la premiere ligne, puis le corps.
        if (! preg_match('/^\s*Objet\s*:\s*(.+?)\R+(.+)$/su', $texte, $m)) {
            throw new RuntimeException('Réponse NVIDIA hors format : '.mb_substr($texte, 0, 200));
        }

        return ['objet' => mb_substr(trim($m[1]), 0, 255), 'corps' => trim($m[2]), 'modele' => $reponse['model']];
    }
}
