<?php

namespace App\Actions\Recherche;

use App\Jobs\AnalyserMedia;
use App\Models\Media;
use App\Models\User;
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

    /** @return int nombre de visuels mis en file */
    public function __invoke(int $taille = self::TAILLE): int
    {
        $medias = self::eligibles()
            ->whereNull('media.analysed_at')
            ->where(fn (Builder $q) => $q->whereNull('media.ai_status')->orWhere('media.ai_status', '!=', 'erreur'))
            ->orderByDesc(User::select('home_selection_at')->whereColumn('users.id', 'media.user_id'))
            ->orderByDesc('media.id')
            ->limit($taille)
            ->get();

        $medias->each(fn (Media $m) => AnalyserMedia::dispatch($m));

        return $medias->count();
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
                    ->orWhere(fn (Builder $p) => $p->whereNotNull('plan')->where('plan_expires_at', '>', now()))));
    }
}
