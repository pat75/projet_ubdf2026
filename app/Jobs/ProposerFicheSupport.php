<?php

namespace App\Jobs;

use App\Models\SupportTicket;
use App\Services\Support\BaseConnaissance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/** Apres une reponse du support : propose une fiche a la base de connaissances. */
class ProposerFicheSupport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(public readonly SupportTicket $ticket) {}

    public function handle(BaseConnaissance $base): void
    {
        $base->proposerFiche($this->ticket);
    }
}
