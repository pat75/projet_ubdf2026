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
            $reponse = $this->appeler($this->messages($this->imageEnDataUrl($media)));
            $resultat = self::lireReponse((string) ($reponse['data']['choices'][0]['message']['content'] ?? ''));
        } catch (NvidiaEnPause $e) {
            throw $e; // le visuel reste en attente
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

    /** NVIDIA (gratuit) d'abord ; OpenRouter (payant) si l'image est refusee ou si NVIDIA n'est pas configure. */
    private function appeler(array $messages): array
    {
        // Modele en place, puis les autres modeles vision NVIDIA : une saturation
        // (503 ResourceExhausted) ne touche souvent qu'un modele.
        if (Nvidia::actif()) {
            if ($reste = Nvidia::pauseRestante()) {
                throw new NvidiaEnPause("NVIDIA injoignable, nouvel essai dans {$reste} s");
            }
            // Secours en 30 s : un modele qui ne repond pas ne bloque pas le lot.
            $panne = true;
            foreach (array_unique([Nvidia::modele(), ...Nvidia::MODELES_VISION]) as $i => $modele) {
                try {
                    $reponse = $this->nvidia->chat($messages, $modele, timeout: $i ? 30 : 90);
                    Nvidia::noterAnalyse();

                    return $reponse;
                } catch (RuntimeException $e) {
                    report($e);
                    $panne = $panne && preg_match(Nvidia::INDISPONIBLE, $e->getMessage());
                }
            }
            // Service en panne : pause de Nvidia::PAUSE minutes, sans OpenRouter.
            // Image refusee par les modeles : secours OpenRouter.
            if ($panne) {
                Nvidia::noterAnalyse($e->getMessage());
                throw new NvidiaEnPause($e->getMessage(), previous: $e);
            }
        }

        return $this->selecteur->chatCompletions(
            messages: $messages,
            costLevel: 1,
            options: ['response_format' => ['type' => 'json_object'], 'timeout' => 60],
            capability: 'vision',
        );
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

        return [
            [
                'role' => 'system',
                'content' => "Tu indexes le visuel d'un portfolio de creatif (illustrateur, photographe, graphiste...) pour un moteur de recherche. ".
                    'Reponds uniquement en JSON : {"titre": "...", "description": "...", "tags_fr": [...], "tags_en": [...]}. '.
                    'titre : 3 a 8 mots, en francais. description : une seule phrase, en francais. '.
                    'tags_fr et tags_en : 3 a 8 mots-cles chacun, en minuscules, au singulier, du plus pertinent au moins pertinent : '.
                    'sujet, technique, style, usage possible. '.
                    "Reprends si possible les domaines du site : {$domaines}. ".
                    "Pas de nom de personne, pas de marque, pas de jugement de valeur.",
            ],
            [
                'role' => 'user',
                'content' => [['type' => 'image_url', 'image_url' => ['url' => $image]]],
            ],
        ];
    }
}
