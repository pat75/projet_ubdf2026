<?php

namespace App\Console\Commands;

use App\Models\CoachMessage;
use App\Models\CreatifActivity;
use App\Models\User;
use App\Services\Coach\Diagnostic;
use App\Services\Coach\Redacteur;
use Illuminate\Console\Command;
use Throwable;

/**
 * Prepare un brouillon de coaching 24 h apres la derniere session d'un
 * createur. L'envoi reste a la main d'un administrateur (page Coach crea).
 */
class PreparerCoachingCommand extends Command
{
    protected $signature = 'ubdf:preparer-coaching';

    protected $description = 'Prépare les messages de coaching des créatifs (24 h après leur dernière session)';

    /** Pause au-dela de laquelle une nouvelle session commence. */
    public const PAUSE_SESSION_MINUTES = 50;

    public const DELAI_HEURES = 24;

    /** Au plus un message par createur sur cette periode (brouillon ou envoye). */
    public const INTERVALLE_JOURS = 7;

    public function handle(Diagnostic $diagnostic, Redacteur $redacteur): int
    {
        // Derniere operation de chaque createur, vieille d'au moins 24 h mais
        // pas plus de 48 h : une execution horaire ne la rate pas, et un vieux
        // journal ne declenche rien au deploiement.
        $dernieres = CreatifActivity::query()
            ->selectRaw('user_id, max(created_at) as derniere')
            ->groupBy('user_id')
            ->havingRaw('max(created_at) <= ? and max(created_at) > ?', [now()->subHours(self::DELAI_HEURES), now()->subHours(2 * self::DELAI_HEURES)])
            ->pluck('derniere', 'user_id');

        foreach ($dernieres as $userId => $derniere) {
            $creatif = User::with('bookSetting')->whereNull('blocked_at')->find($userId);

            if (! $creatif || $creatif->bookSetting?->coaching === false || $this->dejaCoache($creatif)) {
                continue;
            }

            $conseils = $diagnostic->pour($creatif);
            $session = $this->derniereSession($creatif);

            try {
                $message = $redacteur->rediger($creatif, $session, $conseils);
            } catch (Throwable $e) {
                $this->warn("{$creatif->login} : {$e->getMessage()}");

                continue;
            }

            CoachMessage::create(['user_id' => $creatif->id, 'session_fin' => $derniere, 'diagnostic' => $conseils, ...$message]);
            $this->info("Brouillon prêt pour {$creatif->login}");
        }

        return self::SUCCESS;
    }

    private function dejaCoache(User $creatif): bool
    {
        return CoachMessage::where('user_id', $creatif->id)
            ->where('statut', '!=', 'ignore')
            ->where('created_at', '>', now()->subDays(self::INTERVALLE_JOURS))
            ->exists();
    }

    /** Operations de la derniere session : on remonte tant que l'ecart reste sous la pause. */
    private function derniereSession(User $creatif)
    {
        $operations = CreatifActivity::where('user_id', $creatif->id)->latest('created_at')->latest('id')->limit(200)->get();
        $session = collect();

        foreach ($operations as $op) {
            if ($session->isNotEmpty() && $session->last()->created_at->diffInMinutes($op->created_at, true) > self::PAUSE_SESSION_MINUTES) {
                break;
            }
            $session->push($op);
        }

        return $session->reverse()->values();
    }
}
