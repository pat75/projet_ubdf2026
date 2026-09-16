<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Normalizer;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garantit que toute entree utilisateur est de l'UTF-8 valide et normalise.
 *
 * Dernier maillon de la politique d'encodage : la base est en utf8mb4, les
 * reponses sont annoncees en UTF-8, mais rien n'empeche un client d'envoyer
 * autre chose. Deux cas concrets sur une plateforme ouverte a l'international :
 *
 *   - un formulaire poste en Windows-1252 par un vieux client : les octets
 *     invalides feraient echouer l'insertion ou corrompraient la valeur ;
 *   - un nom copie depuis macOS arrive en forme decomposee (NFD), ou « é »
 *     s'ecrit « e » + accent combinant. Visuellement identique, mais une autre
 *     suite d'octets : la recherche et les comparaisons echouent en silence.
 *
 * Les deux sont ramenes a de l'UTF-8 en forme composee (NFC), qui est la
 * forme attendue par MySQL et par le reste de l'application.
 */
class NormalizeUnicodeInput
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->merge($this->normalize($request->input()));

        return $next($request);
    }

    /**
     * @param  array<array-key, mixed>  $input
     * @return array<array-key, mixed>
     */
    private function normalize(array $input): array
    {
        foreach ($input as $key => $value) {
            $input[$key] = match (true) {
                is_array($value) => $this->normalize($value),
                is_string($value) => $this->normalizeString($value),
                default => $value,
            };
        }

        return $input;
    }

    private function normalizeString(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        if (! mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
        }

        if (class_exists(Normalizer::class) && ! Normalizer::isNormalized($value, Normalizer::FORM_C)) {
            $value = Normalizer::normalize($value, Normalizer::FORM_C) ?: $value;
        }

        return $value;
    }
}
