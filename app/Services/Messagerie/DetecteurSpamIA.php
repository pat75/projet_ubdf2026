<?php

namespace App\Services\Messagerie;

use App\Models\Conversation;
use App\Services\IA\Decisions\DecisionsClient;
use RuntimeException;

/**
 * Evalue si le premier message d'une conversation est probablement un
 * spam, via le modele de decision configure (config('messagerie.spam_filter')).
 *
 * Complementaire a DetecteurSpam (regles, listes, quotas) : celui-ci
 * masque une demande suspecte des sa reception (`is_spam`), celui-ci se
 * contente de proposer une probabilite affichee en label — jamais de
 * masquage automatique, jamais de decision prise a la place du createur.
 */
class DetecteurSpamIA
{
    public function __construct(private readonly DecisionsClient $decisions)
    {
    }

    public function actif(): bool
    {
        return (bool) config('messagerie.spam_filter.active');
    }

    public function seuil(): float
    {
        return (float) config('messagerie.spam_filter.seuil');
    }

    /**
     * Evalue le premier message recu d'une conversation et enregistre le
     * resultat (`spam_ia_probabilite`, `spam_ia`). Renvoie false si rien
     * n'a pu etre evalue (detection coupee, pas de message, appel en
     * echec) : la conversation reste alors a evaluer.
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
     * Probabilite calibree (0 a 1) que le message soit un spam ou une
     * sollicitation commerciale non pertinente, ou null si l'appel a
     * echoue (modele indisponible, cle absente...).
     */
    public function evaluer(string $message): ?float
    {
        try {
            $reponses = $this->decisions->demander(
                modele: (string) config('messagerie.spam_filter.modele'),
                etat: ['message' => $message],
                questions: [
                    'spam' => [
                        'type' => 'noul',
                        'instructions' => "Ce message a ete recu via le formulaire de contact d'une plateforme ".
                            'de mise en relation entre createurs freelances (illustrateurs, graphistes) et '.
                            'clients potentiels. Est-ce un spam ou une sollicitation commerciale non '.
                            'pertinente (demarchage, hameconnage, offre de service sans rapport avec une '.
                            'prestation creative, lien suspect) plutot qu\'une demande de travail authentique ?',
                    ],
                ],
            );
        } catch (RuntimeException) {
            return null;
        }

        $valeur = $reponses['spam']['noul'] ?? null;

        return is_numeric($valeur) ? (float) $valeur : null;
    }
}
