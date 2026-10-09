<?php

namespace App\Services\IA;

use App\Models\BookSetting;
use App\Models\Media;
use App\Models\Tag;
use App\Services\Images\Declinaison;
use App\Services\Images\GenerateurImages;
use App\Support\DossierBook;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Titre, description d'une ligne et 3 a 8 mots-cles (fr et en) d'un
 * visuel, pour le moteur de recherche. L'image part en base64 dans sa
 * declinaison `ptf_medium` (550px) : moins de tokens, et pas besoin
 * qu'OpenRouter atteigne le site.
 */
class AnalyseImage
{
    private const DECLINAISON = 'ptf_medium';

    public function __construct(
        private readonly OpenRouterModelSelector $selecteur,
        private readonly GenerateurImages $generateur,
        private readonly Nvidia $nvidia,
    ) {}

    public function analyser(Media $media): void
    {
        try {
            [$reponse, $resultat] = $this->appeler($this->messages($this->imageEnDataUrl($media)));
        } catch (RuntimeException $e) {
            $media->forceFill(['ai_status' => 'erreur'])->saveQuietly();

            throw $e;
        }

        DB::transaction(function () use ($media, $resultat, $reponse) {
            // Accord relu sous verrou : retire pendant l'appel IA, rien n'est ecrit.
            if (! BookSetting::where('user_id', $media->user_id)->lockForUpdate()->value('allow_ai_analysis')) {
                return;
            }

            $ids = [];
            foreach (['fr', 'en'] as $lang) {
                foreach ($resultat["tags_{$lang}"] as $label) {
                    $ids[] = Tag::firstOrCreate(['label' => $label, 'lang' => $lang])->id;
                }
            }
            $media->tags()->sync($ids);

            $media->forceFill([
                'ai_title' => $resultat['titre'],
                'ai_description' => $resultat['description'],
                'ai_status' => 'ok',
                'ai_model' => $reponse['model'],
                'analysed_at' => now(),
            ])->saveQuietly();
        });
    }

    /**
     * NVIDIA (gratuit) d'abord ; OpenRouter (payant) si l'image est refusee,
     * si NVIDIA n'est pas configure ou s'il ne repond plus. Une panne met
     * NVIDIA en pause (Nvidia::PAUSE minutes) : pendant ce temps, tout passe
     * par OpenRouter sans retenter NVIDIA a chaque visuel.
     *
     * Une reponse hors format compte comme un refus : on passe au modele
     * suivant plutot que de laisser le visuel en erreur.
     *
     * @return array{0: array, 1: array{titre: string, description: string, tags_fr: list<string>, tags_en: list<string>}}
     */
    private function appeler(array $messages): array
    {
        $lire = fn (array $reponse) => [$reponse, self::lireReponse(self::contenu($reponse))];

        // Modele en place, puis les autres modeles vision NVIDIA : une saturation
        // (503 ResourceExhausted) ne touche souvent qu'un modele.
        if (Nvidia::actif() && ! Nvidia::pauseRestante()) {
            // Secours en 30 s : un modele qui ne repond pas ne bloque pas le lot.
            $panne = true;
            foreach (array_unique([Nvidia::modele(), ...Nvidia::MODELES_VISION]) as $i => $modele) {
                try {
                    $reponse = $this->nvidia->chat($messages, $modele, timeout: $i ? 30 : 90);
                    Nvidia::noterAnalyse();
                } catch (RuntimeException $e) {
                    report($e);
                    $panne = $panne && preg_match(Nvidia::INDISPONIBLE, $e->getMessage());

                    continue;
                }

                // Le service repond : ce n'est pas une panne, meme si le format est faux.
                $panne = false;

                try {
                    return $lire($reponse);
                } catch (RuntimeException $e) {
                    report(new RuntimeException("{$modele} : {$e->getMessage()}", previous: $e));
                }
            }
            // Service en panne : pause de Nvidia::PAUSE minutes. Panne ou image
            // refusee, le visuel passe dans tous les cas par OpenRouter.
            if ($panne) {
                Nvidia::noterAnalyse($e->getMessage());
            }
        }

        return $lire($this->selecteur->chatCompletions(
            messages: $messages,
            costLevel: 1,
            options: ['response_format' => ['type' => 'json_object'], 'timeout' => 60],
            capability: 'vision',
        ));
    }

    /** Texte de la reponse ; certains modeles le rendent en liste de blocs {type, text}. */
    private static function contenu(array $reponse): string
    {
        $contenu = $reponse['data']['choices'][0]['message']['content'] ?? '';

        return is_array($contenu)
            ? implode('', array_map(fn ($bloc) => is_array($bloc) ? (string) ($bloc['text'] ?? '') : (string) $bloc, $contenu))
            : (string) $contenu;
    }

    /**
     * Valide et normalise la reponse du modele.
     *
     * @return array{titre: string, description: string, tags_fr: list<string>, tags_en: list<string>}
     */
    public static function lireReponse(string $contenu): array
    {
        // Certains modeles entourent le JSON d'une cloture markdown ou d'une phrase malgre la consigne.
        $contenu = preg_match('/\{.*\}/s', $contenu, $m) ? $m[0] : trim($contenu);
        $json = json_decode($contenu, true);

        if (! is_array($json) || empty($json['titre'])) {
            throw new RuntimeException('Reponse IA illisible : '.mb_substr($contenu, 0, 200));
        }

        $tags = fn (mixed $liste) => array_slice(array_values(array_unique(array_filter(
            array_map(fn ($t) => is_string($t) ? Tag::normaliser($t) : '', (array) $liste)
        ))), 0, 8);

        $resultat = [
            'titre' => mb_substr(trim((string) $json['titre']), 0, 255),
            'description' => mb_substr(trim((string) ($json['description'] ?? '')), 0, 255),
            'tags_fr' => $tags($json['tags_fr'] ?? []),
            'tags_en' => $tags($json['tags_en'] ?? []),
        ];

        if (count($resultat['tags_fr']) < 3) {
            throw new RuntimeException('Moins de 3 mots-cles renvoyes.');
        }

        return $resultat;
    }

    private function imageEnDataUrl(Media $media): string
    {
        $chemin = $this->generateur->produire(
            DossierBook::chemin($media->user->login, $media->filename),
            Declinaison::nommee(self::DECLINAISON),
        );

        if ($chemin === null || ! is_file($chemin)) {
            throw new RuntimeException("Visuel introuvable : media {$media->id}.");
        }

        return 'data:'.mime_content_type($chemin).';base64,'.base64_encode(file_get_contents($chemin));
    }

    private function messages(string $image): array
    {
        $motcles = json_decode(file_get_contents(resource_path('js/portail/motcles.json')), true);
        $domaines = implode(', ', array_keys($motcles['fr'] ?? []));

        // Consigne dans le message utilisateur, a cote de l'image : Llama 3.2
        // Vision ignore le message systeme des qu'une image est jointe, et
        // repondait alors par une legende libre (« Here is the caption… »).
        $consigne = "Tu indexes ce visuel du portfolio d'un créatif (illustrateur, photographe, graphiste…) pour un moteur de recherche.\n\n"
            ."Réponds UNIQUEMENT par un objet JSON, sans phrase avant ni après, sans bloc de code :\n"
            .'{"titre": "<titre>", "description": "<phrase>", "tags_fr": ["<mot-clé>", "..."], "tags_en": ["<keyword>", "..."]}'."\n\n"
            ."Règles :\n"
            ."- titre : 3 à 8 mots, en français, ce que montre l'image.\n"
            ."- description : une seule phrase en français : sujet, technique, style.\n"
            ."- tags_fr et tags_en : 3 à 8 mots-clés chacun, en minuscules, au singulier, du plus pertinent au moins pertinent : sujet, technique, style, usage possible. tags_en est la traduction anglaise de tags_fr.\n"
            ."- Reprends si possible les domaines du site : {$domaines}.\n"
            ."- Pas de nom de personne, pas de marque, pas de jugement de valeur (« beau », « magnifique »…).\n"
            ."- Remplace chaque <…> par ce que tu vois dans l'image reçue.";

        return [
            [
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => $consigne],
                    ['type' => 'image_url', 'image_url' => ['url' => $image]],
                ],
            ],
        ];
    }
}
