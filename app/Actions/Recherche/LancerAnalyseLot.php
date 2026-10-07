<?php

namespace App\Actions\Recherche;

use App\Jobs\AnalyserMedia;
use App\Models\Media;
use App\Models\User;
use App\Services\IA\NvidiaEnPause;
use Illuminate\Database\Eloquent\Builder;

/**
 * Met en file les prochains visuels a analyser : ceux des books de la
 * selection ou en formule payante active, dont le creatif a autorise
 * l'analyse, hors portfolios proteges. Les books les plus recemment
 * selectionnes passent en premier.
 */
class LancerAnalyseLot
{
    public const TAILLE = 10;

    public const BOOKS = 10;

    /**
     * Analyse les visuels tout de suite, dans la requete : pas de worker a
     * faire tourner pour un lot lance a la main.
     *
     * ponytail: ~5 s par image, soit ~1 min pour 10 ; repasser par la
     * file (AnalyserMedia::dispatch) si les lots grossissent.
     *
     * $avant(Media, rang, total) est appele avant chaque visuel (progression).
     * $books : toutes les images restantes des $books prochains books, au
     * lieu des $taille prochaines images.
     *
     * @return array{ok: int, erreurs: int, pause: ?string}
     */
    public function __invoke(int $taille = self::TAILLE, ?callable $avant = null, ?int $books = null): array
    {
        set_time_limit(0);

        $aFaire = fn () => self::enAttente()
            ->orderByDesc(User::select('home_selection_at')->whereColumn('users.id', 'media.user_id'));

        $medias = $books
            ? $aFaire()->whereIn('media.user_id', $aFaire()->select('media.user_id')->distinct()->limit($books)->pluck('user_id'))
                ->orderBy('media.user_id')->orderByDesc('media.id')->get()
            : $aFaire()->orderByDesc('media.id')->limit($taille)->get();

        $erreurs = 0;
        foreach ($medias->values() as $i => $media) {
            if ($avant) {
                $avant($media, $i + 1, $medias->count());
            }
            try {
                AnalyserMedia::dispatchSync($media);
            } catch (NvidiaEnPause $e) {
                // NVIDIA injoignable : le reste du lot attend le prochain passage.
                return ['ok' => $i - $erreurs, 'erreurs' => $erreurs, 'pause' => $e->getMessage()];
            } catch (\Throwable $e) {
                // Le visuel est marque « erreur » et sort des lots suivants.
                report($e);
                $erreurs++;
            }
        }

        return ['ok' => $medias->count() - $erreurs, 'erreurs' => $erreurs, 'pause' => null];
    }

    /** Visuels eligibles pas encore analyses, hors erreurs. */
    public static function enAttente(): Builder
    {
        return self::eligibles()
            ->whereNull('media.analysed_at')
            ->where(fn (Builder $q) => $q->whereNull('media.ai_status')->orWhere('media.ai_status', '!=', 'erreur'));
    }

    /** Visuels qui peuvent etre analyses, deja faits compris. */
    public static function eligibles(): Builder
    {
        return Media::query()
            ->published()
            ->horsProteges()
            ->where('filename', '!=', '')
            ->whereHas('user', fn (Builder $u) => $u
                ->whereHas('bookSetting', fn (Builder $b) => $b->where('allow_ai_analysis', true))
                ->where(fn (Builder $f) => $f
                    ->where('in_home_selection', true)
                    ->orWhere(fn (Builder $p) => $p->where('plan', '>', 0)->where('plan_expires_at', '>', now()))));
    }
}
