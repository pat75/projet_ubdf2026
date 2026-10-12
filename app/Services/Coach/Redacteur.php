<?php

namespace App\Services\Coach;

use App\Models\CoachMessage;
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
     * Rédige le message ; la version IA est gardée à part (objet_ia,
     * corps_ia) pour mesurer ensuite ce que l'administrateur a corrigé.
     *
     * @param  Collection<int, CreatifActivity>  $operations
     * @param  list<string>  $conseils
     * @return array{objet: string, corps: string, objet_ia: string, corps_ia: string, modele: string}
     */
    public function rediger(User $creatif, Collection $operations, array $conseils): array
    {
        $message = $this->lire($this->appeler($this->messages($creatif, $operations, $conseils)));

        return [...$message, 'objet_ia' => $message['objet'], 'corps_ia' => $message['corps']];
    }

    /**
     * Le prompt exact envoyé à l'IA : coach.md, le guide appris des
     * corrections, les corrections du même conseil, puis le contexte.
     * Affiché tel quel dans « Mémoire du coach ».
     *
     * @param  Collection<int, CreatifActivity>  $operations
     * @param  list<string>  $conseils
     * @return list<array{role: string, content: string}>
     */
    public function messages(User $creatif, Collection $operations, array $conseils): array
    {
        $systeme = file_get_contents(resource_path('prompts/coach.md'));

        if ($regles = Reglage::texte(Reglage::COACH_REGLES)) {
            $systeme .= "\n\n## Règles apprises des corrections de l'équipe (à respecter toujours)\n\n{$regles}";
        }

        $exemples = CoachMessage::exemples($conseils[0] ?? null);
        if ($exemples->isNotEmpty()) {
            $systeme .= "\n\n## Corrections faites par l'équipe sur des messages précédents\n\n"
                ."Imite le ton et les choix de la version envoyée, jamais son contenu propre au créatif.\n\n"
                .$exemples->map(fn (CoachMessage $m) => self::couple($m))->implode("\n\n");
        }

        $contexte = "Créatif : {$creatif->fullName()}\n"
            ."Book : {$creatif->bookUrl()}\n\n"
            ."Opérations de la dernière session :\n- ".$operations->map->resume()->unique()->implode("\n- ")."\n\n"
            // Un seul conseil, le plus important (Diagnostic les classe) : le
            // message propose une amelioration, pas une liste de taches.
            ."Conseil à transmettre (n'en ajoute aucun) : ".($conseils[0] ?? '');

        return [
            ['role' => 'system', 'content' => $systeme],
            ['role' => 'user', 'content' => $contexte],
        ];
    }

    /**
     * Met le guide de style à jour avec de nouvelles corrections
     * (resources/prompts/coach-apprentissage.md). Guide vide : on repart de zéro.
     *
     * @param  iterable<CoachMessage>  $corriges
     */
    public function apprendre(iterable $corriges, ?string $guide = null): string
    {
        $guide ??= Reglage::texte(Reglage::COACH_REGLES) ?? '';
        $couples = collect($corriges)->map(fn (CoachMessage $m) => self::couple($m))->implode("\n\n");

        $reponse = $this->appeler([
            ['role' => 'system', 'content' => file_get_contents(resource_path('prompts/coach-apprentissage.md'))],
            ['role' => 'user', 'content' => "Guide actuel :\n".($guide ?: '(vide)')."\n\nCorrections :\n\n{$couples}"],
        ]);

        $regles = collect(preg_split('/\R/u', $reponse['data']['choices'][0]['message']['content'] ?? ''))
            ->map(fn ($l) => trim($l))->filter(fn ($l) => str_starts_with($l, '- '))->take(30)->implode("\n");

        if ($regles === '') {
            throw new RuntimeException("Guide renvoyé vide par {$reponse['model']}");
        }

        Reglage::definirTexte(Reglage::COACH_REGLES, $regles);

        return $regles;
    }

    /** Reconstruit le guide depuis toute la mémoire, par lots de 20. */
    public function reconstruire(): string
    {
        $guide = '';
        Reglage::definirTexte(Reglage::COACH_REGLES, null); // memoire vide : plus de regles
        foreach (CoachMessage::corriges()->oldest('envoye_le')->get()->chunk(20) as $lot) {
            $guide = $this->apprendre($lot, $guide);
        }

        return $guide;
    }

    private static function couple(CoachMessage $m): string
    {
        return "### Version IA\nObjet : {$m->objet_ia}\n\n{$m->corps_ia}\n\n### Version envoyée\nObjet : {$m->objet}\n\n{$m->corps}";
    }

    /**
     * Relecture d'un brouillon : syntaxe et tournures seulement, sans
     * toucher au fond (resources/prompts/coach-relecture.md).
     *
     * @return array{objet: string, corps: string, modele: string}
     */
    public function relire(string $objet, string $corps): array
    {
        return $this->lire($this->appeler([
            ['role' => 'system', 'content' => file_get_contents(resource_path('prompts/coach-relecture.md'))],
            ['role' => 'user', 'content' => "Objet : {$objet}\n\n{$corps}"],
        ]));
    }

    /** @return array{objet: string, corps: string, modele: string} */
    private function lire(array $reponse): array
    {
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
