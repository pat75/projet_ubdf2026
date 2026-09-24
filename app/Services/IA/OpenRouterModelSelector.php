<?php

namespace App\Services\IA;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Repris de ultra-book diffusion (ubdiff_www), sans modification de la
 * logique : un modele candidat echoue (quota, modele retire...), le
 * suivant de la meme fourchette de cout prend le relais. Point d'entree
 * unique pour toutes les operations IA du site (correction de message,
 * et ce qui suivra).
 */
class OpenRouterModelSelector
{
    private const API_URL = 'https://openrouter.ai/api/v1/chat/completions';

    private const TEXT_MODELS_BY_COST = [
        1 => [
            // Modele employe en premier : bascule sur la suite de la
            // fourchette si OpenRouter le retire ou change son identifiant.
            'deepseek/deepseek-v4.1-flash',
            'openai/gpt-4o-mini',
            'google/gemini-2.0-flash-lite-001',
            'meta-llama/llama-3.3-70b-instruct:free',
        ],
        2 => [
            'google/gemini-2.0-flash-001',
            'openai/gpt-4o-mini',
            'anthropic/claude-3.5-haiku',
        ],
        3 => [
            'openai/gpt-4o',
            'anthropic/claude-3.7-sonnet',
            'google/gemini-2.5-flash-preview',
        ],
        4 => [
            'anthropic/claude-3.7-sonnet',
            'openai/gpt-4.1',
            'google/gemini-2.5-pro-preview',
        ],
    ];

    private const VISION_MODELS_BY_COST = [
        1 => [
            'google/gemini-2.0-flash-lite-001',
            'openai/gpt-4o-mini',
        ],
        2 => [
            'google/gemini-2.0-flash-001',
            'openai/gpt-4o-mini',
        ],
        3 => [
            'openai/gpt-4o',
            'google/gemini-2.5-flash-preview',
        ],
        4 => [
            'openai/gpt-4.1',
            'anthropic/claude-3.7-sonnet',
        ],
    ];

    public function chatCompletions(
        array $messages,
        int $costLevel = 1,
        ?string $defaultModel = null,
        array $options = [],
        string $capability = 'text'
    ): array {
        $apiKey = config('services.openrouter.api_key');

        if (!$apiKey) {
            throw new RuntimeException('Clé OpenRouter non configurée.');
        }

        $costLevel = max(1, min(4, $costLevel));
        $candidateModels = $this->resolveCandidateModels($costLevel, $defaultModel, $capability);
        $attempts = [];
        $lastError = 'Aucun modèle tenté.';

        foreach ($candidateModels as $model) {
            $response = $this->sendRequest($apiKey, $model, $messages, $options);
            $errorMessage = $this->extractErrorMessage($response);

            $attempts[] = [
                'model' => $model,
                'status' => $response->status(),
                'success' => $response->successful(),
                'error' => $errorMessage,
            ];

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['choices'][0]['message']['content'])) {
                    Log::info('OpenRouter modèle sélectionné', [
                        'requested_default_model' => $defaultModel,
                        'selected_model' => $model,
                        'cost_level' => $costLevel,
                        'capability' => $capability,
                        'fallback_used' => $model !== $defaultModel,
                    ]);

                    Log::channel('modelselector')->info('Changement de modèle', [
                        'date' => now()->toDateTimeString(),
                        'cout' => $costLevel,
                        'modele' => $model,
                    ]);

                    return [
                        'model' => $model,
                        'data' => $data,
                        'attempts' => $attempts,
                        'fallback_used' => $model !== $defaultModel,
                    ];
                }

                $lastError = 'Réponse OpenRouter invalide pour le modèle ' . $model . '.';
                continue;
            }

            $lastError = $errorMessage;

            if ($this->isFatalConfigurationError($response)) {
                break;
            }
        }

        throw new RuntimeException($lastError);
    }

    private function resolveCandidateModels(int $costLevel, ?string $defaultModel, string $capability): array
    {
        $matrix = $capability === 'vision'
            ? self::VISION_MODELS_BY_COST
            : self::TEXT_MODELS_BY_COST;

        $orderedLevels = [$costLevel];
        foreach ([1, 2, 3, 4] as $level) {
            if (!in_array($level, $orderedLevels, true)) {
                $orderedLevels[] = $level;
            }
        }

        $models = [];

        if ($defaultModel) {
            $models[] = $defaultModel;
        }

        foreach ($orderedLevels as $level) {
            foreach ($matrix[$level] ?? [] as $model) {
                $models[] = $model;
            }
        }

        return array_values(array_unique(array_filter($models)));
    }

    private function sendRequest(string $apiKey, string $model, array $messages, array $options): Response
    {
        $httpTimeout = (int) ($options['timeout'] ?? 30);
        unset($options['timeout']);

        $payload = array_merge([
            'model' => $model,
            'messages' => $messages,
        ], $options);

        return Http::timeout($httpTimeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => url('/'),
                'X-Title' => config('app.name'),
            ])
            ->post(self::API_URL, $payload);
    }

    private function extractErrorMessage(Response $response): string
    {
        $json = $response->json();

        if (is_array($json)) {
            return $json['error']['message']
                ?? $json['message']
                ?? $response->body()
                ?? 'Erreur OpenRouter inconnue.';
        }

        return $response->body() ?: 'Erreur OpenRouter inconnue.';
    }

    private function isFatalConfigurationError(Response $response): bool
    {
        return in_array($response->status(), [401, 403], true);
    }
}
