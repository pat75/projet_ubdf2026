<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\Response;

/**
 * Microbook : vignette du book a integrer dans un autre site par iframe
 * (`/microbook_<admin>_<pied>__<login>`, ultrabook2012_nanobookiframe du
 * legacy). L'URL est celle du legacy : elle est collee dans des sites tiers.
 */
class MicrobookController extends Controller
{
    public function __invoke(string $admin, string $pied, string $login): Response
    {
        $creatif = User::where('login', $login)->with(['bookSetting', 'category'])->first();

        abort_unless($creatif && $creatif->bookSetting?->diffuse_web, 404);

        // Comme le legacy : les 10 premiers visuels de la premiere galerie,
        // completes par la deuxieme s'il en manque.
        $visuels = collect();
        foreach ($creatif->galleries()->published()->whereNull('parent_id')->orderBy('position')->limit(2)->get() as $galerie) {
            $ordre = array_flip($galerie->media_order ?? []);
            $visuels = $visuels->concat($galerie->media()->published()->where('filename', '<>', '')->get()
                ->sortBy(fn (Media $m) => [$ordre[(string) ($m->legacy_id ?? $m->id)] ?? PHP_INT_MAX, $m->legacy_id ?? $m->id]));

            if ($visuels->count() >= 10) {
                break;
            }
        }

        return response()->view('front.microbook', [
            'creatif' => $creatif,
            'visuels' => $visuels->take(10)->values(),
            'avecPied' => $pied === '1',
        ])->header('Content-Security-Policy', 'frame-ancestors *');
    }
}
