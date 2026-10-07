<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Remet l'accueil dans l'etat du legacy, apres un transfert :
 *
 * --dates  dates de selection (inc_stats.st_selection_date), seul critere
 *          de tri des « dernieres selections » du legacy. Le transfert du
 *          6 octobre 2026 avait date toutes les selections de l'import.
 * --stats  vues et coeurs des mini-books, lus sur le serveur de stats du
 *          legacy (st_book, st_memo) : ils n'etaient pas dans la base.
 */
class RepriseAccueilLegacyCommand extends Command
{
    protected $signature = 'ubdf:legacy:accueil
        {--dates : Reprend les dates de selection depuis inc_stats}
        {--stats : Reprend vues et coeurs depuis le serveur de stats du legacy}
        {--simuler : N\'ecrit rien, affiche ce qui serait fait}';

    protected $description = 'Dates de selection, vues et coeurs des books repris du legacy';

    private const API_STATS = 'https://www.extra-book.com/2012_stats/st_action.php';

    /** Au-dela d'une cinquantaine, le serveur de stats repond 500. */
    private const LOGINS_PAR_APPEL = 20;

    public function handle(): int
    {
        if (! $this->option('dates') && ! $this->option('stats')) {
            $this->error('Preciser --dates et/ou --stats.');

            return self::INVALID;
        }

        if ($this->option('dates')) {
            $this->dates();
        }
        if ($this->option('stats')) {
            $this->stats();
        }

        return self::SUCCESS;
    }

    private function dates(): void
    {
        // Date la plus recente par compte legacy ; « 0000-00-00 » n'est pas une date.
        $parLegacy = DB::connection('legacy')->table('inc_stats')
            ->where('st_selection_date', '>', '1970-01-01')
            ->groupBy('st_id_user')
            ->pluck(DB::raw('MAX(st_selection_date)'), 'st_id_user');

        $selection = User::where('in_home_selection', true)->whereNotNull('legacy_id')->pluck('id', 'legacy_id');
        $datees = 0;

        foreach ($selection as $legacyId => $id) {
            $date = $parLegacy[$legacyId] ?? null;
            $datees += $date ? 1 : 0;

            if (! $this->option('simuler')) {
                // Sans evenement : le hook `saving` de User redaterait de l'instant.
                User::whereKey($id)->update(['home_selection_at' => $date]);
            }
        }

        $this->info("Dates de selection : {$datees} reprises, ".($selection->count() - $datees).' sans date (en fin de liste).');
    }

    private function stats(): void
    {
        // Le serveur de stats connait les logins d'origine, pas ceux convertis.
        // Sans whereIn : 66 000 identifiants depassent la limite de MySQL
        // (65 535 parametres par requete).
        $ids = User::whereNotNull('legacy_id')->pluck('id', 'legacy_id');
        $logins = DB::connection('legacy')->table('inc_user')
            ->pluck('us_login', 'us_id')
            ->intersectByKeys($ids);

        $barre = $this->output->createProgressBar($logins->count());
        $repris = 0;

        $echecs = 0;

        foreach ($logins->chunk(self::LOGINS_PAR_APPEL, true) as $paquet) {
            // Un paquet refuse est repris login par login : un seul login
            // fautif ne doit pas priver les autres de leurs chiffres.
            $stats = $this->appelStats($paquet->values()->all())
                ?? $paquet->values()->map(fn ($login) => $this->appelStats([$login])[0] ?? null)->all();

            foreach ($paquet->keys()->values() as $i => $legacyId) {
                $s = $stats[$i] ?? null;
                if (! is_array($s)) {
                    $echecs++;

                    continue;
                }
                $repris++;
                if (! $this->option('simuler')) {
                    User::whereKey($ids[$legacyId])->update([
                        'legacy_views' => max(0, (int) ($s['st_book'] ?? 0)),
                        'legacy_likes' => max(0, (int) ($s['st_memo'] ?? 0)),
                    ]);
                }
            }
            $barre->advance($paquet->count());
        }

        $barre->finish();
        $this->newLine();
        $this->info("Vues et coeurs : {$repris} books repris sur {$logins->count()}, {$echecs} sans chiffres (inconnus du serveur ou en erreur).");
    }

    /**
     * JSONP du serveur de stats : cb([...]); — une entree par login, dans
     * l'ordre, false si inconnu. Null si l'appel echoue.
     *
     * @param  list<string>  $logins
     */
    private function appelStats(array $logins): ?array
    {
        try {
            $reponse = Http::retry(2, 1000, throw: false)->timeout(30)->get(self::API_STATS, [
                'action' => 'stats_aff_book',
                'st_champs' => 'st_minibook, st_memo',
                'us_login' => json_encode($logins),
                'st_cles' => '',
                'jsoncallback' => 'cb',
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException) {
            return null;
        }

        if (! $reponse->successful()) {
            return null;
        }

        $stats = json_decode(preg_replace('/^cb\((.*)\);?\s*$/s', '$1', $reponse->body()), true);

        return is_array($stats) ? $stats : null;
    }
}
