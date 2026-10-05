<?php

namespace App\Services\Messagerie;

use App\Models\Conversation;
use App\Services\IA\Decisions\DecisionsClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Evalue si le premier message d'une conversation est probablement un
 * spam, avec Jev (TypeSafe) via OpenRouter : les alternatives de
 * config('messagerie.spam_filter.modeles') sont essayees dans l'ordre.
 *
 * Si aucune ne repond, Jev est repute indisponible : l'analyse est
 * suspendue quelques minutes (`pause`) au lieu de rappeler l'API a chaque
 * affichage, et la liste des conversations du back-office le signale.
 *
 * Complementaire a DetecteurSpam (regles, listes, quotas) : celui-ci
 * masque une demande suspecte des sa reception (`is_spam`), celui-ci se
 * contente de proposer une probabilite affichee en label.
 */
class DetecteurSpamIA
{
    private const CLE_INDISPONIBLE = 'spam_ia.indisponible';

    private const API_CHAT = 'https://openrouter.ai/api/v1/chat/completions';

    private const CONSIGNE = "Ce message a ete recu via le formulaire de contact d'une plateforme ".
        'de mise en relation entre createurs freelances (illustrateurs, graphistes, photographes) et '.
        'clients potentiels. Est-ce un spam ou une sollicitation commerciale non pertinente '.
        '(demarchage, hameconnage, arnaque au trop-percu, offre de service sans rapport avec une '.
        'prestation creative, lien suspect) plutot qu\'une demande de travail authentique ?';

    public function __construct(private readonly DecisionsClient $decisions)
    {
    }

    /** Detection activee en configuration et Jev disponible. */
    public function actif(): bool
    {
        return (bool) config('messagerie.spam_filter.active') && $this->indisponibilite() === null;
    }

    public function seuil(): float
    {
        return (float) config('messagerie.spam_filter.seuil');
    }

    /**
     * Jev ne repond plus : depuis quand, jusqu'a quand l'analyse est
     * suspendue, et la derniere erreur. Null si tout va bien.
     *
     * @return array{depuis: string, reprise: string, erreur: string}|null
     */
    public function indisponibilite(): ?array
    {
        return Cache::get(self::CLE_INDISPONIBLE);
    }

    /**
     * Evalue le premier message recu d'une conversation et enregistre le
     * resultat (`spam_ia_probabilite`, `spam_ia`). Renvoie false si rien
     * n'a pu etre evalue : la conversation reste alors a evaluer.
     */
    public function analyser(Conversation $conversation): bool
    {
        if (! $this->actif()) {
            return false;
        }

        $premier = $conversation->messages()->where('from_owner', false)->oldest()->first();

        if (! $premier) {
            return false;
        }

        $probabilite = $this->evaluer($premier->body);

        if ($probabilite === null) {
            return false;
        }

        $conversation->forceFill([
            'spam_ia_probabilite' => $probabilite,
            'spam_ia' => $probabilite >= $this->seuil(),
        ])->save();

        return true;
    }

    /**
     * Probabilite (0 a 1) que le message soit un spam, ou null si aucune
     * alternative Jev n'a repondu — l'analyse est alors suspendue.
     */
    public function evaluer(string $message): ?float
    {
        $message = mb_substr($message, 0, 4000);
        $derniereErreur = 'Aucun modèle configuré.';

        foreach ((array) config('messagerie.spam_filter.modeles', []) as $alternative) {
            try {
                $valeur = ($alternative['api'] ?? 'decisions') === 'chat'
                    ? $this->parChat($alternative['modele'], $message)
                    : $this->parDecisions($alternative['modele'], $message);
            } catch (RuntimeException|ConnectionException $e) {
                $derniereErreur = $alternative['modele'].' : '.$e->getMessage();

                continue;
            }

            if ($valeur !== null) {
                Cache::forget(self::CLE_INDISPONIBLE);

                return max(0.0, min(1.0, $valeur));
            }

            $derniereErreur = $alternative['modele'].' : réponse illisible.';
        }

        $this->suspendre($derniereErreur);

        return null;
    }

    private function parDecisions(string $modele, string $message): ?float
    {
        $reponses = $this->decisions->demander(
            modele: $modele,
            etat: ['message' => $message],
            questions: ['spam' => ['type' => 'noul', 'instructions' => self::CONSIGNE]],
        );

        $valeur = $reponses['spam']['noul'] ?? null;

        return is_numeric($valeur) ? (float) $valeur : null;
    }

    private function parChat(string $modele, string $message): ?float
    {
        $cle = config('services.openrouter.api_key');

        if (! $cle) {
            throw new RuntimeException('Clé OpenRouter non configurée.');
        }

        $reponse = Http::timeout(30)->withToken($cle)->post(self::API_CHAT, [
            'model' => $modele,
            'temperature' => 0,
            'messages' => [
                ['role' => 'system', 'content' => self::CONSIGNE.' Reponds uniquement en JSON : {"probabilite": nombre entre 0 et 1}.'],
                ['role' => 'user', 'content' => $message],
            ],
        ]);

        if (! $reponse->successful()) {
            throw new RuntimeException((string) ($reponse->json('error.message') ?? 'HTTP '.$reponse->status()));
        }

        $contenu = (string) $reponse->json('choices.0.message.content');

        // Certains modeles entourent le JSON de ```json ... ``` : on prend l'objet.
        if (preg_match('/\{.*\}/s', $contenu, $m)) {
            $contenu = $m[0];
        }

        $valeur = json_decode($contenu, true)['probabilite'] ?? null;

        return is_numeric($valeur) ? (float) $valeur : null;
    }

    private function suspendre(string $erreur): void
    {
        $minutes = max(1, (int) config('messagerie.spam_filter.pause', 30));

        Cache::put(self::CLE_INDISPONIBLE, [
            'depuis' => now()->format('d/m/Y H:i'),
            'reprise' => now()->addMinutes($minutes)->format('H:i'),
            'erreur' => $erreur,
        ], now()->addMinutes($minutes));

        Log::channel('modelselector')->warning('Jev indisponible : analyse des spams suspendue', ['erreur' => $erreur]);
    }
}
