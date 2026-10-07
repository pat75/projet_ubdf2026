<?php

namespace App\Services\IA;

use App\Models\Reglage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

/**
 * API NVIDIA (build.nvidia.com), gratuite mais limitee : 40 requetes par
 * minute pour tout le site. Cle, expiration et modele se reglent dans
 * App\Filament\Pages\ReglageNvidia.
 *
 * Le catalogue /v1/models cite des modeles qui ne sont pas deployes (404
 * a l'appel) : la validite d'un modele se juge donc par un vrai appel.
 */
class Nvidia
{
    public const URL = 'https://integrate.api.nvidia.com/v1/chat/completions';

    public const PAR_MINUTE = 40;

    /** Modeles vision qui repondaient le 2026-10-07, du meilleur au moins bon. */
    public const MODELES_VISION = [
        'nvidia/nemotron-3-nano-omni-30b-a3b-reasoning',
        'meta/llama-3.2-11b-vision-instruct',
        'meta/llama-3.2-90b-vision-instruct',
        'google/gemma-4-31b-it',
    ];

    private const VERIFICATION = 'nvidia_verification';

    public static function cle(): ?string
    {
        $chiffree = Reglage::texte(Reglage::NVIDIA_CLE);

        return $chiffree ? Crypt::decryptString($chiffree) : config('services.nvidia.api_key');
    }

    public static function definirCle(string $cle): void
    {
        Reglage::definirTexte(Reglage::NVIDIA_CLE, Crypt::encryptString(trim($cle)));
    }

    public static function modele(): string
    {
        return Reglage::texte(Reglage::NVIDIA_MODELE) ?: self::MODELES_VISION[0];
    }

    public static function actif(): bool
    {
        return filled(self::cle());
    }

    /** @return array{model: string, data: array} */
    public function chat(array $messages, ?string $modele = null, int $timeout = 90): array
    {
        $cle = self::cle() ?? throw new RuntimeException('Clé NVIDIA non configurée.');
        $modele ??= self::modele();

        $this->attendreCreneau();

        try {
            $reponse = Http::timeout($timeout)->withToken($cle)->acceptJson()->post(self::URL, [
                'model' => $modele,
                'messages' => $messages,
                // Les modeles « reasoning » consomment des tokens avant de repondre.
                'max_tokens' => 2048,
            ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException("NVIDIA {$modele} : {$e->getMessage()}", previous: $e);
        }

        if (! $reponse->successful() || ! isset($reponse['choices'][0]['message']['content'])) {
            throw new RuntimeException("NVIDIA {$modele} : HTTP {$reponse->status()} ".mb_substr($reponse->body(), 0, 200));
        }

        return ['model' => $modele, 'data' => $reponse->json()];
    }

    /**
     * Appel reel avec une petite image. Le resultat du modele en place est
     * garde pour l'affichage de la page de reglage.
     *
     * @return array{modele: string, valide: bool, motif: string, date: string}
     */
    public function verifier(?string $modele = null): array
    {
        $modele ??= self::modele();
        $debut = microtime(true);

        try {
            $this->chat([[
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => 'Quelle couleur ? Un mot.'],
                    ['type' => 'image_url', 'image_url' => ['url' => self::imageTest()]],
                ],
            ]], $modele, timeout: 45);
            $etat = ['valide' => true, 'motif' => sprintf('répond en %.1f s', microtime(true) - $debut)];
        } catch (RuntimeException $e) {
            $etat = ['valide' => false, 'motif' => mb_substr($e->getMessage(), 0, 200)];
        }

        $etat = ['modele' => $modele, ...$etat, 'date' => now()->format('d/m/Y H:i')];
        if ($modele === self::modele()) {
            Cache::put(self::VERIFICATION, $etat, now()->addMonth());
        }

        return $etat;
    }

    public static function derniereVerification(): ?array
    {
        return Cache::get(self::VERIFICATION);
    }

    /** Premier modele de la liste, autre que $sauf, qui repond vraiment. */
    public function remplacant(string $sauf): ?string
    {
        foreach (self::MODELES_VISION as $modele) {
            if ($modele !== $sauf && $this->verifier($modele)['valide']) {
                return $modele;
            }
        }

        return null;
    }

    /**
     * Bloque jusqu'a ce qu'un creneau se libere : les lots passent au
     * rythme permis, quel que soit le nombre de workers.
     *
     * ponytail: attente bloquante (une minute au pire) ; passer au
     * middleware RateLimited si un worker doit rester libre.
     */
    private function attendreCreneau(): void
    {
        while (! RateLimiter::attempt('nvidia', self::PAR_MINUTE, fn () => true, 60)) {
            sleep(max(1, RateLimiter::availableIn('nvidia')));
        }
    }

    /** Carre rouge 64px : certains modeles refusent les images minuscules. */
    private static function imageTest(): string
    {
        $image = imagecreatetruecolor(64, 64);
        imagefill($image, 0, 0, imagecolorallocate($image, 220, 30, 30));
        ob_start();
        imagepng($image);

        return 'data:image/png;base64,'.base64_encode(ob_get_clean());
    }
}
