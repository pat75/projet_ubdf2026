<?php

namespace App\Services\IA;

/**
 * Correction orthographique/grammaticale d'un texte redige par le
 * createur, via OpenRouterModelSelector. Le texte corrige est propose,
 * jamais impose : c'est a l'appelant de decider de l'utiliser.
 */
class CorrectionMessage
{
    public function __construct(private readonly OpenRouterModelSelector $selecteur)
    {
    }

    public function corriger(string $texte): string
    {
        $reponse = $this->selecteur->chatCompletions(
            messages: [
                [
                    'role' => 'system',
                    'content' => "Tu corriges l'orthographe, la grammaire et la ponctuation du message ci-dessous, ".
                        "en français. Ne change ni le sens, ni le ton, ni la mise en forme (sauts de ligne). ".
                        'Réponds uniquement avec le texte corrigé, sans commentaire ni guillemets.',
                ],
                ['role' => 'user', 'content' => $texte],
            ],
            costLevel: 1,
        );

        $corrige = trim((string) ($reponse['data']['choices'][0]['message']['content'] ?? ''));

        return $corrige !== '' ? $corrige : $texte;
    }
}
