<?php

namespace App\Jobs;

use App\Models\Media;
use App\Services\IA\AnalyseImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/** Analyse IA d'un visuel, lancee par lot depuis l'admin (LancerAnalyseLot). */
class AnalyserMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $backoff = 30;

    public function __construct(public readonly Media $media) {}

    public function handle(AnalyseImage $analyse): void
    {
        // Consentement retire entre le lancement et l'execution.
        if (! $this->media->user->bookSetting?->allow_ai_analysis) {
            return;
        }

        $analyse->analyser($this->media);
    }
}
