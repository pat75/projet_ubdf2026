<?php

namespace App\Services\Coach;

use App\Models\CreatifActivity;
use App\Models\Reglage;
use App\Models\User;
use App\Services\IA\Nvidia;
use App\Services\IA\OpenRouterModelSelector;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Redige le message de coaching a partir de resources/prompts/coach.md,
 * avec l'IA reglee sur la page Coach creatif (Reglage::COACH_IA) : NVIDIA
 * (gratuit) par defaut, OpenRouter (payant) a un niveau de cout choisi.
 * NVIDIA absent ou en panne : OpenRouter au niveau 1, le moins cher.
 */
class Redacteur
{
    /** Modele texte : seul teste le 2026-10-08 a repondre (9 s) dans un francais propre et au format attendu. */
    public const MODELE = 'nvidia/nemotron-3-super-120b-a12b';

    /** Choix du selecteur de la page Coach creatif. */
    public const CHOIX = [
        'nvidia' => 'NVIDIA · '.self::MODELE.' (gratuit)',
        '1' => 'OpenRouter · coût 1 (économique)',
        '2' => 'OpenRouter · coût 2',
        '3' => 'OpenRouter · coût 3',
        '4' => 'OpenRouter · coût 4 (premium)',
    ];

    public function __construct(private Nvidia $nvidia, private OpenRouterModelSelector $openRouter) {}

    public static function choix(): string
    {
        $choix = Reglage::texte(Reglage::COACH_IA);

        return isset(self::CHOIX[$choix]) ? $choix : 'nvidia';
    }

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

        $reponse = $this->appeler([
            ['role' => 'system', 'content' => file_get_contents(resource_path('prompts/coach.md'))],
            ['role' => 'user', 'content' => $contexte],
        ]);

        $texte = trim($reponse['data']['choices'][0]['message']['content'] ?? '');

        // Format attendu : « Objet : … » sur la premiere ligne, puis le corps.
        if (! preg_match('/^\s*Objet\s*:\s*(.+?)\R+(.+)$/su', $texte, $m)) {
            throw new RuntimeException("Réponse {$reponse['model']} hors format : ".mb_substr($texte, 0, 200));
        }

        return ['objet' => mb_substr(trim($m[1]), 0, 255), 'corps' => trim($m[2]), 'modele' => $reponse['model']];
    }

    /** @return array{model: string, data: array} */
    private function appeler(array $messages): array
    {
        $choix = self::choix();

        if ($choix === 'nvidia' && Nvidia::actif()) {
            try {
                return $this->nvidia->chat($messages, self::MODELE);
            } catch (RuntimeException $e) {
                report($e); // panne NVIDIA : OpenRouter prend le relais
            }
        }

        return $this->openRouter->chatCompletions($messages, costLevel: $choix === 'nvidia' ? 1 : (int) $choix);
    }
}
